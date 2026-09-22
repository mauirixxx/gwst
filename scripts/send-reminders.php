<?php
/**
 * GWTTT scheduled reminder runner.
 *
 * Usage:
 *   php scripts/send-reminders.php --dry-run
 *   php scripts/send-reminders.php --send
 */

if (PHP_SAPI !== 'cli') {
    http_response_code(403);
    exit("CLI only.\n");
}

$options = getopt('', array('dry-run', 'send'));
$dryRun = isset($options['dry-run']);
$send = isset($options['send']);
if ($dryRun === $send) {
    fwrite(STDERR, "Usage: php scripts/send-reminders.php --dry-run|--send\n");
    exit(2);
}

$root = dirname(__DIR__);
require_once $root . '/connect.php';
require_once $root . '/includes/mailer.php';

$con = mysqli_connect(DATABASE_HOST, DATABASE_USER, DATABASE_PASS, DATABASE_NAME);
if (!$con || $con->connect_errno) {
    fwrite(STDERR, "Unable to connect to database.\n");
    exit(1);
}
$con->set_charset('utf8mb4');

function out(string $message): void { echo $message . PHP_EOL; }

out('GWTTT scheduled reminders');
out('Database: ' . DATABASE_NAME);
out('Mode: ' . ($dryRun ? 'DRY RUN (no mail, no ledger writes)' : 'SEND'));
out('Treasure e-mail threshold: 31 days after latest collection');
out(str_repeat('-', 72));

$sql = <<<'SQL'
SELECT
    h.treasure_history_id,
    h.userid,
    u.username,
    u.usermail,
    h.charid,
    c.charname,
    h.location_id,
    l.location_name,
    h.collected_on,
    DATE_ADD(h.collected_on, INTERVAL 31 DAY) AS reminder_on
FROM gwtreasure_history h
JOIN (
    SELECT userid, charid, location_id, MAX(collected_on) AS latest_collected_on
    FROM gwtreasure_history
    GROUP BY userid, charid, location_id
) latest
  ON latest.userid = h.userid
 AND latest.charid = h.charid
 AND latest.location_id = h.location_id
 AND latest.latest_collected_on = h.collected_on
JOIN (
    SELECT userid, charid, location_id, collected_on, MAX(treasure_history_id) AS latest_history_id
    FROM gwtreasure_history
    GROUP BY userid, charid, location_id, collected_on
) tie
  ON tie.userid = h.userid
 AND tie.charid = h.charid
 AND tie.location_id = h.location_id
 AND tie.collected_on = h.collected_on
 AND tie.latest_history_id = h.treasure_history_id
JOIN userinfo u ON u.userid = h.userid
JOIN gwchars c ON c.charid = h.charid AND c.userid = h.userid
JOIN gwtreasure_locations l ON l.location_id = h.location_id
JOIN user_preferences p ON p.userid = h.userid AND p.treasure_email_enabled = 1
LEFT JOIN reminder_notifications n
  ON n.userid = h.userid
 AND n.reminder_type = 'treasure'
 AND n.reference_key = CONCAT('treasure:', h.treasure_history_id)
WHERE DATE_ADD(h.collected_on, INTERVAL 31 DAY) <= CURDATE()
  AND n.notification_id IS NULL
ORDER BY h.userid, c.charname, l.display_order, l.location_name
SQL;

$result = $con->query($sql);
if (!$result) {
    fwrite(STDERR, 'Treasure reminder query failed: ' . $con->error . PHP_EOL);
    exit(1);
}

$users = array();
while ($row = $result->fetch_assoc()) {
    $uid = (int)$row['userid'];
    if (!isset($users[$uid])) {
        $users[$uid] = array(
            'username' => $row['username'],
            'email' => $row['usermail'],
            'treasures' => array(),
        );
    }
    $users[$uid]['treasures'][] = $row;
}
$result->free();

if (!$users) {
    out('No treasure reminders are due.');
    exit(0);
}

$ledger = $con->prepare("INSERT IGNORE INTO reminder_notifications (userid, reminder_type, reference_key) VALUES (?, 'treasure', ?)");
if (!$ledger) {
    fwrite(STDERR, 'Unable to prepare reminder ledger statement: ' . $con->error . PHP_EOL);
    exit(1);
}

$sentUsers = 0;
$failedUsers = 0;
$totalItems = 0;

foreach ($users as $userid => $user) {
    $count = count($user['treasures']);
    $totalItems += $count;
    out(sprintf('[user %d] %s <%s> — %d treasure reminder%s', $userid, $user['username'], $user['email'], $count, $count === 1 ? '' : 's'));

    $lines = array(
        'Aloha ' . $user['username'] . ',',
        '',
        'The following Guild Wars treasures are ready to collect again (31-day reminder buffer):',
        '',
    );
    $htmlItems = '';
    foreach ($user['treasures'] as $row) {
        $line = sprintf('%s — %s (last collected %s; reminder date %s)', $row['charname'], $row['location_name'], $row['collected_on'], $row['reminder_on']);
        out('  - ' . $line . ' [history #' . $row['treasure_history_id'] . ']');
        $lines[] = '- ' . $line;
        $htmlItems .= '<li><strong>' . htmlspecialchars($row['charname'], ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8') . '</strong> — '
            . htmlspecialchars($row['location_name'], ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8')
            . ' <small>(last collected ' . htmlspecialchars($row['collected_on'], ENT_QUOTES, 'UTF-8') . ')</small></li>';
    }
    $lines[] = '';
    $lines[] = 'Log in to GWTTT to record your next collection.';
    $text = implode(PHP_EOL, $lines);
    $html = '<p>Aloha ' . htmlspecialchars($user['username'], ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8') . ',</p>'
        . '<p>The following Guild Wars treasures are ready to collect again (31-day reminder buffer):</p><ul>'
        . $htmlItems . '</ul><p>Log in to GWTTT to record your next collection.</p>';

    if ($dryRun) {
        out('  DRY RUN: would send one consolidated e-mail; ledger unchanged.');
        continue;
    }

    $mail = gwst_send_mail($con, $user['email'], 'GWTTT: Guild Wars treasures ready to collect', $text, $html);
    if (!$mail['success']) {
        ++$failedUsers;
        out('  SEND FAILED: ' . $mail['message']);
        continue;
    }

    $con->begin_transaction();
    try {
        foreach ($user['treasures'] as $row) {
            $reference = 'treasure:' . (int)$row['treasure_history_id'];
            $ledger->bind_param('is', $userid, $reference);
            if (!$ledger->execute()) {
                throw new RuntimeException($ledger->error);
            }
        }
        $con->commit();
        ++$sentUsers;
        out('  SENT: e-mail accepted; reminder ledger updated.');
    } catch (Throwable $e) {
        $con->rollback();
        ++$failedUsers;
        error_log('GWTTT reminder ledger update failed after successful mail: ' . $e->getMessage());
        out('  WARNING: mail sent, but ledger update failed. Check server log before rerunning.');
    }
}

$ledger->close();
out(str_repeat('-', 72));
out(sprintf('Treasure candidates: %d across %d user(s).', $totalItems, count($users)));
if ($dryRun) {
    out('Dry run complete. No e-mail was sent and no reminder ledger rows were written.');
} else {
    out(sprintf('Send complete. Successful users: %d; failed users: %d.', $sentUsers, $failedUsers));
}
