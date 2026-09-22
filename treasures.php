<?php
$pagetitle = 'Treasure Tracker';
include_once('header.php');

if (empty($_SESSION['userid'])) {
    exit;
}

$userid = (int) $_SESSION['userid'];
$accid = isset($_SESSION['prefaccid']) ? (int) $_SESSION['prefaccid'] : 0;
$charid = isset($_SESSION['prefcharid']) ? (int) $_SESSION['prefcharid'] : 0;
$charname = isset($_SESSION['prefcharname']) ? (string) $_SESSION['prefcharname'] : '';

$characterValid = false;
if ($accid > 0 && $charid > 0) {
    $check = $con->prepare('SELECT 1 FROM gwchars WHERE charid = ? AND accid = ? AND userid = ? LIMIT 1');
    $check->bind_param('iii', $charid, $accid, $userid);
    $check->execute();
    $characterValid = (bool) $check->get_result()->fetch_row();
    $check->close();
}
?>
<section class="treasure-page">
    <div class="treasure-heading">
        <h1>Treasure Tracker</h1>
        <?php if ($characterValid): ?>
            <p>Treasure availability for <strong><?php echo h($charname); ?></strong>.</p>
        <?php else: ?>
            <p>Select an account and character in the header to view treasure availability.</p>
        <?php endif; ?>
    </div>

<?php if ($characterValid): ?>
    <div class="treasure-grid">
<?php
    $stmt = $con->prepare(
        'SELECT l.location_id, l.location_name, l.wiki_url, l.reset_days, '
        . 'MAX(h.collected_on) AS last_collected, '
        . 'DATEDIFF(CURDATE(), MAX(h.collected_on)) AS days_since '
        . 'FROM gwtreasure_locations l '
        . 'LEFT JOIN gwtreasure_history h '
        . 'ON h.location_id = l.location_id AND h.userid = ? AND h.charid = ? '
        . 'WHERE l.is_active = 1 '
        . 'GROUP BY l.location_id, l.location_name, l.wiki_url, l.reset_days, l.display_order '
        . 'ORDER BY l.display_order, l.location_name'
    );
    $stmt->bind_param('ii', $userid, $charid);
    $stmt->execute();
    $result = $stmt->get_result();

    while ($row = $result->fetch_assoc()) {
        $lastCollected = $row['last_collected'];
        $resetDays = (int) $row['reset_days'];
        $daysSince = $row['days_since'] === null ? null : (int) $row['days_since'];

        if ($lastCollected === null) {
            $status = 'Never collected';
            $statusClass = 'never';
        } elseif ($daysSince >= $resetDays) {
            $status = 'Ready now';
            $statusClass = 'ready';
        } else {
            $remaining = $resetDays - $daysSince;
            $status = $remaining . ' day' . ($remaining === 1 ? '' : 's') . ' remaining';
            $statusClass = 'waiting';
        }
?>
        <article class="treasure-location treasure-<?php echo h($statusClass); ?>">
            <div>
                <?php if (!empty($row['wiki_url'])): ?>
                    <a href="<?php echo h($row['wiki_url']); ?>" target="_blank" rel="noopener noreferrer"><strong><?php echo h($row['location_name']); ?></strong></a>
                <?php else: ?>
                    <strong><?php echo h($row['location_name']); ?></strong>
                <?php endif; ?>
                <?php if ($lastCollected !== null): ?>
                    <small>Last collected <?php echo h($lastCollected); ?></small>
                <?php endif; ?>
            </div>
            <span class="treasure-status"><?php echo h($status); ?></span>
        </article>
<?php
    }
    $stmt->close();
?>
    </div>
    <p class="treasure-note">A location becomes ready again 30 days after that character's most recent recorded collection.</p>
<?php endif; ?>
</section>
<?php include_once('footer.php'); ?>
