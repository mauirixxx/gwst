<?php
if (isset($_SESSION['userid'])) {
	// get all the character id's related to the selected account id's
	// $gci = Get Character ID's
	$gaccid = implode(", ", $_POST['delaccid']);
    $gci = $con->prepare("SELECT charid, accid FROM gwchars WHERE accid IN ($gaccid) AND userid = ?");
	$gci->bind_param("i", $_SESSION['userid']);
    $gci->execute();
    $gciresults = $gci->get_result();
    while ($gcirow = $gciresults->fetch_assoc()) {
        $delchar = $con->prepare("DELETE FROM gwchars WHERE charid = ? AND accid = ? AND userid = ?");
        $delchar->bind_param("iii", $gcirow['charid'], $gcirow['accid'], $_SESSION['userid']);
        $delchar->execute();
		$delchar->close();
		// $dac = Delete Account Stats
		$dac = $con->prepare("DELETE FROM gwaccstats WHERE accid = ? AND userid = ?");
		$dac->bind_param("ii", $gcirow['accid'], $_SESSION['userid']);
        $dac->execute();
		$dac->close();
        // $dcs = Delete Character Stats
        $dcs = $con->prepare("DELETE FROM gwcharstats WHERE charid = ? AND accid = ? AND userid = ?");
        $dcs->bind_param("iii", $gcirow['charid'], $gcirow['accid'], $_SESSION['userid']);
        $dcs->execute();
        $dcs->close();
    }
    $gci->close();
	//this should be the last SQL query to run!
    $delacc = $con->prepare("DELETE FROM gwaccounts WHERE accid IN ($gaccid) AND userid = ?");
    $delacc->bind_param("i", $_SESSION['userid']);
    $delacc->execute();
    $delacc->close();
	// $nap = No Account Preference
	$nap = $con->prepare("UPDATE userinfo SET prefaccid = 0, prefaccname = 'No default selected' WHERE userid = ?");
    $nap->bind_param("i", $_SESSION['userid']);
    $nap->execute();
    $nap->close();
    $_SESSION['prefaccid'] = "0";
    $_SESSION['preaccname'] = "No default selected";
    echo 'Account(s) deleted - no preferred account selected.<br /><br />';
	// $ncp = No Character Preference
	$ncp = $con->prepare("UPDATE userinfo SET prefcharid = 0, prefcharname = 'No default selected' WHERE userid = ?");
    $ncp->bind_param("i", $_SESSION['userid']);
    $ncp->execute();
    $ncp->close();
    $_SESSION['prefcharid'] = "0";
    $_SESSION['prefcharname'] = "No default selected";
    echo 'All characters related to the account have been deleted - no preferred character selected.<br /><br />';
}
?>