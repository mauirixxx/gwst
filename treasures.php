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
            <p>Treasure availability for <strong><?php echo h($charname); ?></strong>. Click a location to record a collection.</p>
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
        . 'DATE_ADD(MAX(h.collected_on), INTERVAL l.reset_days DAY) AS ready_on, '
        . 'DATEDIFF(DATE_ADD(MAX(h.collected_on), INTERVAL l.reset_days DAY), CURDATE()) AS days_remaining '
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
        $readyOn = $row['ready_on'];
        $daysRemaining = $row['days_remaining'] === null ? null : (int) $row['days_remaining'];

        if ($lastCollected === null) {
            $status = 'Never collected';
            $statusDetail = 'Ready whenever you are';
            $statusClass = 'never';
        } elseif ($daysRemaining <= 0) {
            $status = 'Ready now';
            $statusDetail = 'Available since ' . date('M j, Y', strtotime($readyOn));
            $statusClass = 'ready';
        } else {
            $status = $daysRemaining . ' day' . ($daysRemaining === 1 ? '' : 's') . ' remaining';
            $statusDetail = 'Ready ' . date('M j, Y', strtotime($readyOn));
            $statusClass = 'waiting';
        }
?>
        <a class="treasure-location treasure-<?php echo h($statusClass); ?>" href="treasure-record.php?location=<?php echo (int) $row['location_id']; ?>">
            <div>
                <strong><?php echo h($row['location_name']); ?></strong>
                <?php if ($lastCollected !== null): ?>
                    <small>Last collected <?php echo h(date('M j, Y', strtotime($lastCollected))); ?></small>
                <?php endif; ?>
            </div>
            <span class="treasure-status">
                <?php echo h($status); ?>
                <small><?php echo h($statusDetail); ?></small>
            </span>
        </a>
<?php
    }
    $stmt->close();
?>
    </div>
    <p class="treasure-note">Each location becomes ready again after its configured reset period. Click any location to record another collection.</p>
<?php endif; ?>
</section>
<?php include_once('footer.php'); ?>
