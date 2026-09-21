<?php
if (isset($_SESSION['userid']) && isset($_POST['prefaccid'])) {
	if ($_POST['prefaccid'] === "nopref") {
		// $nap = No AccountID Preferrence
		$nap = $con->prepare("UPDATE userinfo SET prefaccid = 0, prefaccname = 'No default selected' WHERE userid = ?");
		$nap->bind_param("i", $_SESSION['userid']);
		$nap->execute();
		$nap->close();
		$_SESSION['prefaccid'] = "0";
		$_SESSION['prefaccname'] = "No default selected";
		echo 'Account preference update - no preferred account selected.<br />';
	} else {
		$prefaccid = filter_var($_POST['prefaccid'], FILTER_VALIDATE_INT);
		if ($prefaccid === false || $prefaccid < 1) {
			echo 'Invalid account preference.<br />';
			return;
		}
		// $sap = Select AccountID Preferrence
		$sap = $con->prepare("SELECT accid, accemail FROM gwaccounts WHERE accid = ? AND userid = ?");
		$sap->bind_param("ii", $prefaccid, $_SESSION['userid']);
		$sap->execute();
		$result = $sap->get_result();
		if ($row = $result->fetch_assoc()) {
			$uap = $con->prepare("UPDATE userinfo SET prefaccid = ?, prefaccname = ? WHERE userid = ?");
			$uap->bind_param("isi", $prefaccid, $row['accemail'], $_SESSION['userid']);
			$uap->execute();
			$uap->close();
			$_SESSION['prefaccid'] = $row['accid'];
			$_SESSION['prefaccname'] = $row['accemail'];
		} else {
			echo 'Account preference not found.<br />';
			$sap->close();
			return;
		}
		$sap->close();
	}
	$ncp = $con->prepare("UPDATE userinfo SET prefcharid = 0, prefcharname = 'No default selected' WHERE userid = ?");
	$ncp->bind_param("i", $_SESSION['userid']);
	$ncp->execute();
	$ncp->close();
	$_SESSION['prefcharid'] = "0";
	$_SESSION['prefcharname'] = "No default selected";
	$_SESSION['charprofid'] = "0";
	echo 'Guild Wars preferred account updated! <br />';
}
?>