<?php
$pagetitle = 'Treasure Overview';
include_once('header.php');

if (empty($_SESSION['userid'])) {
    exit;
}

$userid = (int) $_SESSION['userid'];
$rows = [];
$readyCount = 0;
$waitingCount = 0;
$neverCount = 0;

$stmt = $con->prepare(
    'SELECT c.charid, c.charname, a.accemail, l.location_id, l.location_name, l.reset_days, '
    . 'MAX(h.collected_on) AS last_collected, '
    . 'DATE_ADD(MAX(h.collected_on), INTERVAL l.reset_days DAY) AS ready_on, '
    . 'DATEDIFF(DATE_ADD(MAX(h.collected_on), INTERVAL l.reset_days DAY), CURDATE()) AS days_remaining '
    . 'FROM gwchars c '
    . 'JOIN gwaccounts a ON a.accid = c.accid AND a.userid = c.userid '
    . 'CROSS JOIN gwtreasure_locations l '
    . 'LEFT JOIN gwtreasure_history h ON h.userid = c.userid AND h.charid = c.charid AND h.location_id = l.location_id '
    . 'WHERE c.userid = ? AND l.is_active = 1 '
    . 'GROUP BY c.charid, c.charname, a.accemail, l.location_id, l.location_name, l.reset_days, l.display_order '
    . 'ORDER BY c.charname, l.display_order, l.location_name'
);
$stmt->bind_param('i', $userid);
$stmt->execute();
$result = $stmt->get_result();

while ($row = $result->fetch_assoc()) {
    $daysRemaining = $row['days_remaining'] === null ? null : (int) $row['days_remaining'];
    if ($row['last_collected'] === null) {
        $row['status'] = 'Never collected';
        $row['status_class'] = 'never';
        $row['status_detail'] = 'Ready whenever you are';
        $neverCount++;
    } elseif ($daysRemaining <= 0) {
        $row['status'] = 'Ready now';
        $row['status_class'] = 'ready';
        $row['status_detail'] = 'Available since ' . date('M j, Y', strtotime($row['ready_on']));
        $readyCount++;
    } else {
        $row['status'] = $daysRemaining . ' day' . ($daysRemaining === 1 ? '' : 's') . ' remaining';
        $row['status_class'] = 'waiting';
        $row['status_detail'] = 'Ready ' . date('M j, Y', strtotime($row['ready_on']));
        $waitingCount++;
    }
    $rows[] = $row;
}
$stmt->close();
?>
<section class="treasure-page treasure-overview-page">
    <div class="treasure-heading">
        <h1>All-Character Treasure Overview</h1>
        <p>Treasure availability across all of your Guild Wars characters.</p>
        <p class="treasure-heading-actions"><a class="treasure-button treasure-button-secondary" href="treasures.php">Back to selected character</a></p>
    </div>

    <div class="treasure-summary treasure-overview-summary">
        <div class="treasure-summary-card treasure-summary-ready"><strong><?php echo number_format($readyCount); ?></strong><span>Ready now</span></div>
        <div class="treasure-summary-card treasure-summary-waiting"><strong><?php echo number_format($waitingCount); ?></strong><span>Waiting</span></div>
        <div class="treasure-summary-card"><strong><?php echo number_format($neverCount); ?></strong><span>Never collected</span></div>
    </div>

    <?php if (!$rows): ?>
        <div class="treasure-empty"><p>No characters or active treasure locations were found.</p></div>
    <?php else: ?>
        <div class="treasure-overview-wrap">
            <table class="treasure-overview-table">
                <thead><tr><th>Character</th><th>Account</th><th>Location</th><th>Last collected</th><th>Status</th></tr></thead>
                <tbody>
                <?php foreach ($rows as $row): ?>
                    <tr class="treasure-overview-<?php echo h($row['status_class']); ?>">
                        <td><strong><?php echo h($row['charname']); ?></strong></td>
                        <td><?php echo h($row['accemail']); ?></td>
                        <td><?php echo h($row['location_name']); ?></td>
                        <td><?php echo $row['last_collected'] ? h(date('M j, Y', strtotime($row['last_collected']))) : '&mdash;'; ?></td>
                        <td class="treasure-overview-status"><strong><?php echo h($row['status']); ?></strong><small><?php echo h($row['status_detail']); ?></small></td>
                    </tr>
                <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    <?php endif; ?>
</section>
<?php include_once('footer.php'); ?>
