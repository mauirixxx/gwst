<?php
if (isset($_SESSION['userid'])) {
    $account_ids = array_values(array_unique(array_filter(
        array_map('intval', $_POST['delaccid'] ?? []),
        function ($id) { return $id > 0; }
    )));

    if (empty($account_ids)) {
        echo 'No accounts selected for deletion.<br /><br />';
        return;
    }

    $placeholders = implode(',', array_fill(0, count($account_ids), '?'));
    $types = str_repeat('i', count($account_ids)) . 'i';
    $params = array_merge($account_ids, [(int)$_SESSION['userid']]);

    // Get all character IDs related to the selected account IDs.
    $gci = $con->prepare("SELECT charid, accid FROM gwchars WHERE accid IN ($placeholders) AND userid = ?");
    $gci->bind_param($types, ...$params);
    $gci->execute();
    $gciresults = $gci->get_result();

    while ($gcirow = $gciresults->fetch_assoc()) {
        $delchar = $con->prepare("DELETE FROM gwchars WHERE charid = ? AND accid = ? AND userid = ?");
        $delchar->bind_param("iii", $gcirow['charid'], $gcirow['accid'], $_SESSION['userid']);
        $delchar->execute();
        $delchar->close();

        // $dac = Delete Account Stats
        $dac = $con->prepare("DELETE FROM gwstats WHERE charid = 0 AND accid = ? AND userid = ?");
        $dac->bind_param("ii", $gcirow['accid'], $_SESSION['userid']);
        $dac->execute();
        $dac->close();

        // $dcs = Delete Character Stats
        $dcs = $con->prepare("DELETE FROM gwstats WHERE charid = ? AND accid = ? AND userid = ?");
        $dcs->bind_param("iii", $gcirow['charid'], $gcirow['accid'], $_SESSION['userid']);
        $dcs->execute();
        $dcs->close();
    }
    $gci->close();

    // This should be the last account-delete SQL query to run.
    $delacc = $con->prepare("DELETE FROM gwaccounts WHERE accid IN ($placeholders) AND userid = ?");
    $delacc->bind_param($types, ...$params);
    $delacc->execute();
    $delacc->close();

    // $nap = No Account Preference
    $nap = $con->prepare("UPDATE userinfo SET prefaccid = 0, prefaccname = 'No default selected' WHERE userid = ?");
    $nap->bind_param("i", $_SESSION['userid']);
    $nap->execute();
    $nap->close();
    $_SESSION['prefaccid'] = "0";
    $_SESSION['prefaccname'] = "No default selected";
    echo 'Account(s) deleted - no preferred account selected.<br /><br />';

    // $ncp = No Character Preference
    $ncp = $con->prepare("UPDATE userinfo SET prefcharid = 0, prefcharname = 'No default selected' WHERE userid = ?");
    $ncp->bind_param("i", $_SESSION['userid']);
    $ncp->execute();
    $ncp->close();
    $_SESSION['prefcharid'] = "0";
    $_SESSION['prefcharname'] = "No default selected";
    $_SESSION['charprofid'] = "0";
    echo 'All characters related to the account have been deleted - no preferred character selected.<br /><br />';
}
?>