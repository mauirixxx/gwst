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

/**
 * Return the observed birthday for a character in a given year.
 * Feb 29 birthdays use Feb 28 in non-leap years.
 */
function birthdayForYear(string $birthdate, int $year): DateTimeImmutable
{
    $monthDay = substr($birthdate, 5, 5);
    if ($monthDay === '02-29' && !checkdate(2, 29, $year)) {
        $monthDay = '02-28';
    }
    return new DateTimeImmutable(sprintf('%04d-%s', $year, $monthDay));
}

out('GWTTT scheduled reminders');
out('Database: ' . DATABASE_NAME);
out('Mode: ' . ($dryRun ? 'DRY RUN (no mail, no ledger writes)' : 'SEND'));
out('Treasure e-mail threshold: 31 days after latest collection');
out('Birthday reminders: user-selected 0, 1, 3, or 7 days before');
out(str_repeat('-', 72));

/* Treasure reminders */
$treasureSql = <<<'SQL'
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

$result = $con->query($treasureSql);
if (!$result) {
    fwrite(STDERR, 'Treasure reminder query failed: ' . $con->error . PHP_EOL);
    exit(1);
}

$treasureUsers = array();
while ($row = $result->fetch_assoc()) {
    $uid = (int)$row['userid'];
    if (!isset($treasureUsers[$uid])) {
        $treasureUsers[$uid] = array('username' => $row['username'], 'email' => $row['usermail'], 'items' => array());
    }
    $treasureUsers[$uid]['items'][] = $row;
}
$result->free();

$treasureLedger = $con->prepare("INSERT IGNORE INTO reminder_notifications (userid, reminder_type, reference_key) VALUES (?, 'treasure', ?)");
if (!$treasureLedger) {
    fwrite(STDERR, 'Unable to prepare treasure ledger statement: ' . $con->error . PHP_EOL);
    exit(1);
}

$treasureSentUsers = 0;
$treasureFailedUsers = 0;
$treasureTotalItems = 0;

if (!$treasureUsers) {
    out('No treasure reminders are due.');
} else {
    foreach ($treasureUsers as $userid => $user) {
        $count = count($user['items']);
        $treasureTotalItems += $count;
        out(sprintf('[treasure user %d] %s <%s> — %d reminder%s', $userid, $user['username'], $user['email'], $count, $count === 1 ? '' : 's'));

        $lines = array('Aloha ' . $user['username'] . ',', '', 'The following Guild Wars treasures are ready to collect again (31-day reminder buffer):', '');
        $htmlItems = '';
        foreach ($user['items'] as $row) {
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
            out('  DRY RUN: would send one consolidated treasure e-mail; ledger unchanged.');
            continue;
        }

        $mail = gwst_send_mail($con, $user['email'], 'GWTTT: Guild Wars treasures ready to collect', $text, $html);
        if (!$mail['success']) {
            ++$treasureFailedUsers;
            out('  SEND FAILED: ' . $mail['message']);
            continue;
        }

        $con->begin_transaction();
        try {
            foreach ($user['items'] as $row) {
                $reference = 'treasure:' . (int)$row['treasure_history_id'];
                $treasureLedger->bind_param('is', $userid, $reference);
                if (!$treasureLedger->execute()) {
                    throw new RuntimeException($treasureLedger->error);
                }
            }
            $con->commit();
            ++$treasureSentUsers;
            out('  SENT: treasure e-mail accepted; reminder ledger updated.');
        } catch (Throwable $e) {
            $con->rollback();
            ++$treasureFailedUsers;
            error_log('GWTTT treasure ledger update failed after successful mail: ' . $e->getMessage());
            out('  WARNING: treasure mail sent, but ledger update failed. Check server log before rerunning.');
        }
    }
}
$treasureLedger->close();

out(str_repeat('-', 72));

/* Birthday reminders */
$birthdaySql = <<<'SQL'
SELECT
    c.charid,
    c.userid,
    c.charname,
    c.birthdate,
    u.username,
    u.usermail,
    p.birthday_reminder_days
FROM gwchars c
JOIN userinfo u ON u.userid = c.userid
JOIN user_preferences p ON p.userid = c.userid AND p.birthday_email_enabled = 1
WHERE c.birthdate IS NOT NULL
ORDER BY c.userid, c.charname
SQL;

$result = $con->query($birthdaySql);
if (!$result) {
    fwrite(STDERR, 'Birthday reminder query failed: ' . $con->error . PHP_EOL);
    exit(1);
}

$today = new DateTimeImmutable('today');
$birthdayUsers = array();
while ($row = $result->fetch_assoc()) {
    $daysBefore = (int)$row['birthday_reminder_days'];
    if (!in_array($daysBefore, array(0, 1, 3, 7), true)) {
        continue;
    }

    $candidateYears = array((int)$today->format('Y'), (int)$today->format('Y') + 1);
    foreach ($candidateYears as $birthdayYear) {
        $birthday = birthdayForYear($row['birthdate'], $birthdayYear);
        $reminderDate = $birthday->modify('-' . $daysBefore . ' days');
        if ($reminderDate->format('Y-m-d') !== $today->format('Y-m-d')) {
            continue;
        }

        $reference = sprintf('birthday:%d:%d:%d', (int)$row['charid'], $birthdayYear, $daysBefore);
        $check = $con->prepare("SELECT notification_id FROM reminder_notifications WHERE userid = ? AND reminder_type = 'birthday' AND reference_key = ? LIMIT 1");
        if (!$check) {
            fwrite(STDERR, 'Unable to prepare birthday ledger check: ' . $con->error . PHP_EOL);
            exit(1);
        }
        $uid = (int)$row['userid'];
        $check->bind_param('is', $uid, $reference);
        $check->execute();
        $alreadySent = $check->get_result()->fetch_assoc();
        $check->close();
        if ($alreadySent) {
            continue;
        }

        if (!isset($birthdayUsers[$uid])) {
            $birthdayUsers[$uid] = array('username' => $row['username'], 'email' => $row['usermail'], 'items' => array());
        }
        $row['birthday_year'] = $birthdayYear;
        $row['birthday_on'] = $birthday->format('Y-m-d');
        $row['reminder_on'] = $reminderDate->format('Y-m-d');
        $row['reference_key'] = $reference;
        $birthdayUsers[$uid]['items'][] = $row;
        break;
    }
}
$result->free();

$birthdayLedger = $con->prepare("INSERT IGNORE INTO reminder_notifications (userid, reminder_type, reference_key) VALUES (?, 'birthday', ?)");
if (!$birthdayLedger) {
    fwrite(STDERR, 'Unable to prepare birthday ledger statement: ' . $con->error . PHP_EOL);
    exit(1);
}

$birthdaySentUsers = 0;
$birthdayFailedUsers = 0;
$birthdayTotalItems = 0;

if (!$birthdayUsers) {
    out('No birthday reminders are due.');
} else {
    foreach ($birthdayUsers as $userid => $user) {
        $count = count($user['items']);
        $birthdayTotalItems += $count;
        out(sprintf('[birthday user %d] %s <%s> — %d reminder%s', $userid, $user['username'], $user['email'], $count, $count === 1 ? '' : 's'));

        $lines = array('Aloha ' . $user['username'] . ',', '', 'Guild Wars character birthday reminder:', '');
        $htmlItems = '';
        foreach ($user['items'] as $row) {
            $daysBefore = (int)$row['birthday_reminder_days'];
            $when = $daysBefore === 0 ? 'today' : ($daysBefore === 1 ? 'tomorrow' : 'in ' . $daysBefore . ' days');
            $line = sprintf('%s — birthday %s (%s)', $row['charname'], $when, $row['birthday_on']);
            out('  - ' . $line . ' [char #' . $row['charid'] . '; ' . $row['reference_key'] . ']');
            $lines[] = '- ' . $line;
            $htmlItems .= '<li><strong>' . htmlspecialchars($row['charname'], ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8') . '</strong> — birthday '
                . htmlspecialchars($when, ENT_QUOTES, 'UTF-8') . ' <small>(' . htmlspecialchars($row['birthday_on'], ENT_QUOTES, 'UTF-8') . ')</small></li>';
        }
        $lines[] = '';
        $lines[] = 'Log in to GWTTT to review your character information.';
        $text = implode(PHP_EOL, $lines);
        $html = '<p>Aloha ' . htmlspecialchars($user['username'], ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8') . ',</p>'
            . '<p>Guild Wars character birthday reminder:</p><ul>' . $htmlItems
            . '</ul><p>Log in to GWTTT to review your character information.</p>';

        if ($dryRun) {
            out('  DRY RUN: would send one consolidated birthday e-mail; ledger unchanged.');
            continue;
        }

        $mail = gwst_send_mail($con, $user['email'], 'GWTTT: Guild Wars character birthday reminder', $text, $html);
        if (!$mail['success']) {
            ++$birthdayFailedUsers;
            out('  SEND FAILED: ' . $mail['message']);
            continue;
        }

        $con->begin_transaction();
        try {
            foreach ($user['items'] as $row) {
                $reference = $row['reference_key'];
                $birthdayLedger->bind_param('is', $userid, $reference);
                if (!$birthdayLedger->execute()) {
                    throw new RuntimeException($birthdayLedger->error);
                }
            }
            $con->commit();
            ++$birthdaySentUsers;
            out('  SENT: birthday e-mail accepted; reminder ledger updated.');
        } catch (Throwable $e) {
            $con->rollback();
            ++$birthdayFailedUsers;
            error_log('GWTTT birthday ledger update failed after successful mail: ' . $e->getMessage());
            out('  WARNING: birthday mail sent, but ledger update failed. Check server log before rerunning.');
        }
    }
}
$birthdayLedger->close();

out(str_repeat('-', 72));
out(sprintf('Treasure candidates: %d across %d user(s).', $treasureTotalItems, count($treasureUsers)));
out(sprintf('Birthday candidates: %d across %d user(s).', $birthdayTotalItems, count($birthdayUsers)));
if ($dryRun) {
    out('Dry run complete. No e-mail was sent and no reminder ledger rows were written.');
} else {
    out(sprintf(
        'Send complete. Treasure users: %d successful/%d failed; birthday users: %d successful/%d failed.',
        $treasureSentUsers,
        $treasureFailedUsers,
        $birthdaySentUsers,
        $birthdayFailedUsers
    ));
}
