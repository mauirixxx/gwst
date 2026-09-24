<?php
$pagetitle = 'Track Miniatures';
include_once ('header.php');
?>
<style>
.mini-page{max-width:980px;margin:0 auto;padding:4px 0 32px}.mini-heading{text-align:center;margin-bottom:22px}.mini-heading h1{margin:0 0 8px}.mini-heading p{margin:5px 0;color:#a9c5d1}.mini-summary{display:grid;grid-template-columns:repeat(2,minmax(0,220px));justify-content:center;gap:14px;margin:0 auto 24px}.mini-summary-card{padding:15px 18px;border:1px solid #2d6978;border-radius:7px;background:#122936;text-align:center}.mini-summary-card strong{display:block;color:#69dbe1;font-size:24px}.mini-summary-card span{color:#a9c5d1;font-size:13px}.mini-group{margin:0 0 12px;border:1px solid #2d596b;border-radius:8px;background:#10232d;overflow:hidden}.mini-group summary{padding:15px 18px;cursor:pointer;list-style:none;color:#69dbe1;font-size:18px;font-weight:700;background:#142e3b}.mini-group summary::-webkit-details-marker{display:none}.mini-group summary::before{content:'▶';display:inline-block;width:22px;font-size:12px;transition:transform .12s ease}.mini-group[open] summary::before{transform:rotate(90deg)}.mini-group-note{margin:0;padding:13px 18px;color:#a9c5d1;border-top:1px solid #294754}.mini-table-wrap{overflow-x:auto}.mini-table{width:100%;border-collapse:collapse}.mini-table th{padding:11px 14px;background:#1a3a4a;color:#eef5f7;text-align:left;font-size:13px;text-transform:uppercase}.mini-table th:nth-child(2),.mini-table th:nth-child(3),.mini-table td:nth-child(2),.mini-table td:nth-child(3){text-align:center}.mini-table td{padding:10px 14px;border-top:1px solid #294754;color:#e1edf1}.mini-table tr:hover td{background:rgba(40,184,192,.06)}.mini-inline-form{margin:0}.mini-dedicated{min-width:68px;padding:7px 12px;border-radius:5px;font-weight:700;cursor:pointer}.mini-yes{border:1px solid #3e9a62;background:#194d31;color:#b9f3cc}.mini-no{border:1px solid #65747c;background:#273740;color:#d7e0e4}.mini-quantity{width:88px;min-height:36px;padding:5px 8px;border:1px solid #607987;border-radius:5px;background:#edf2f5;color:#17242c;font-size:16px}.mini-save-quantity{margin-left:6px;padding:7px 9px;border:1px solid #2999a5;border-radius:4px;background:#174454;color:#fff;cursor:pointer}.mini-no-account{padding:24px;border:1px solid #6e5c31;border-radius:8px;background:#302817;text-align:center;color:#f3e3bd}.mini-account-name{color:#fff27a;font-weight:700}@media(max-width:650px){.mini-summary{grid-template-columns:1fr}.mini-table th,.mini-table td{padding:9px 8px}.mini-save-quantity{display:none}}
</style>
<?php
if (isset($_SESSION['userid'])) {
    $userid = (int)$_SESSION['userid'];
    $accid = (int)($_SESSION['prefaccid'] ?? 0);
    echo '<section class="mini-page"><div class="mini-heading"><h1>Miniature Collection</h1>';

    if ($accid <= 0) {
        echo '<p>Track Hall of Monuments dedication and how many copies you currently have.</p></div>';
        echo '<div class="mini-no-account">Select a Guild Wars account from the Account menu above before tracking miniatures.</div></section>';
        include_once ('footer.php');
        exit;
    }

    $account = $con->prepare('SELECT accemail FROM gwaccounts WHERE accid = ? AND userid = ? LIMIT 1');
    $account->bind_param('ii', $accid, $userid);
    $account->execute();
    $accountRow = $account->get_result()->fetch_assoc();
    $account->close();
    if (!$accountRow) {
        echo '<p>Track Hall of Monuments dedication and how many copies you currently have.</p></div>';
        echo '<div class="mini-no-account">The selected Guild Wars account could not be found.</div></section>';
        include_once ('footer.php');
        exit;
    }

    echo '<p>Tracking miniatures for <span class="mini-account-name">' . h($accountRow['accemail']) . '</span></p>';
    echo '<p>Dedicated status and on-hand quantity are independent: a dedicated miniature can still have an on-hand quantity of zero.</p></div>';

    $summary = $con->prepare('SELECT COALESCE(SUM(dedicated),0) AS dedicated_count, COALESCE(SUM(quantity),0) AS quantity_total FROM gwminiature_inventory WHERE userid = ? AND accid = ?');
    $summary->bind_param('ii', $userid, $accid);
    $summary->execute();
    $summaryRow = $summary->get_result()->fetch_assoc();
    $summary->close();
    echo '<div class="mini-summary"><div class="mini-summary-card"><strong>' . (int)$summaryRow['dedicated_count'] . '</strong><span>distinct miniatures marked dedicated</span></div><div class="mini-summary-card"><strong>' . (int)$summaryRow['quantity_total'] . '</strong><span>miniatures currently on hand</span></div></div>';

    $groups = $con->query('SELECT groupid, groupname, group_note FROM gwminiature_groups ORDER BY display_order, groupname');
    $miniStmt = $con->prepare(
        'SELECT m.miniid, m.mininame, COALESCE(i.dedicated,0) AS dedicated, COALESCE(i.quantity,0) AS quantity '
        . 'FROM gwminiature_group_members gm JOIN gwminiatures m ON m.miniid = gm.miniid '
        . 'LEFT JOIN gwminiature_inventory i ON i.miniid = m.miniid AND i.userid = ? AND i.accid = ? '
        . 'WHERE gm.groupid = ? ORDER BY gm.display_order, m.mininame'
    );

    while ($group = $groups->fetch_assoc()) {
        $groupid = (int)$group['groupid'];
        $miniStmt->bind_param('iii', $userid, $accid, $groupid);
        $miniStmt->execute();
        $miniResult = $miniStmt->get_result();
        echo '<details class="mini-group"><summary>' . h($group['groupname']) . ' <small>(' . $miniResult->num_rows . ')</small></summary>';
        if (!empty($group['group_note'])) echo '<p class="mini-group-note">' . h($group['group_note']) . '</p>';
        if ($miniResult->num_rows === 0) {
            echo '<p class="mini-group-note">No miniatures in this group.</p>';
        } else {
            echo '<div class="mini-table-wrap"><table class="mini-table"><thead><tr><th>Miniature</th><th>HoM Dedicated</th><th>On Hand</th></tr></thead><tbody>';
            while ($mini = $miniResult->fetch_assoc()) {
                $dedicated = (int)$mini['dedicated'] === 1;
                echo '<tr><td>' . h($mini['mininame']) . '</td><td>';
                echo '<form class="mini-inline-form" action="includes/update-miniature.php" method="post">' . csrf_input();
                echo '<input type="hidden" name="miniid" value="' . (int)$mini['miniid'] . '"><input type="hidden" name="action" value="toggle_dedicated">';
                echo '<button class="mini-dedicated ' . ($dedicated ? 'mini-yes' : 'mini-no') . '" type="submit" title="Click to toggle Hall of Monuments dedication">' . ($dedicated ? 'Yes' : 'No') . '</button></form></td><td>';
                echo '<form class="mini-inline-form" action="includes/update-miniature.php" method="post">' . csrf_input();
                echo '<input type="hidden" name="miniid" value="' . (int)$mini['miniid'] . '"><input type="hidden" name="action" value="set_quantity">';
                echo '<input class="mini-quantity" type="number" name="quantity" min="0" max="999999" step="1" inputmode="numeric" value="' . (int)$mini['quantity'] . '" aria-label="On-hand quantity for ' . h($mini['mininame']) . '">';
                echo '<button class="mini-save-quantity" type="submit">Save</button></form></td></tr>';
            }
            echo '</tbody></table></div>';
        }
        echo '</details>';
    }
    $miniStmt->close();
    echo '</section>';
}
include_once ('footer.php');
?>
