<?php
if ($_POST['prefaccid'] == "nopref") {
	$nap = $con->prepare("UPDATE userinfo SET prefaccid = 0, prefaccname = 'No default selected' WHERE userid = ?");
	$nap->bind_param("i", $_SESSION['userid']);
	$nap->execute();
	$nap->close();
	$_SESSION['prefaccid'] = "0";
	$_SESSION['prefaccname'] = "No default selected";
	echo 'Account preference update - no preferred account selected.<br />';
} else {
	$sap = $con->prepare("SELECT accid, accemail FROM gwaccounts WHERE accid = ? AND userid = ?");
	$sap->bind_param("ii", $_POST['prefaccid'], $_SESSION['userid']);
	$sap->execute();
	$result = $sap->get_result();
	while ($row = $result->fetch_assoc()) {
		$uap = $con->prepare("UPDATE userinfo SET prefaccid = ?, prefaccname = ? WHERE userid = ?");
		$uap->bind_param("isi", $_POST['prefaccid'], $row['accemail'], $_SESSION['userid']);
		$uap->execute();
		$uap->close();
		$_SESSION['prefaccid'] = $row['accid'];
		$_SESSION['prefaccname'] = $row['accemail'];
	}
	echo 'Guild Wars preferred account updated! <br />';
}
?>