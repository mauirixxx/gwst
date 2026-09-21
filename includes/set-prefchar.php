<?php
if (isset($_SESSION['userid']) && isset($_POST['prefcharid'])) {
    if ($_POST['prefcharid'] === "nopref") {
		// $ncp = No CharID Preferrence
        $ncp = $con->prepare("UPDATE userinfo SET prefcharid = 0, prefcharname = 'No default selected' WHERE userid = ?");
		$ncp->bind_param("i", $_SESSION['userid']);
		$ncp->execute();
		$ncp->close();
		$_SESSION['prefcharid'] = "0";
		$_SESSION['prefcharname'] = "No default selected";
		$_SESSION['charprofid'] = "0";
	} else {
		$prefcharid = filter_var($_POST['prefcharid'], FILTER_VALIDATE_INT);
		if ($prefcharid === false || $prefcharid < 1) {
			echo 'Invalid character preference.<br />';
			return;
		}
		// $scp = Selected CharID Preferrence
		$scp = $con->prepare("SELECT charid, charname, profid FROM gwchars WHERE charid = ? AND accid = ? AND userid = ?");
		$scp->bind_param("iii", $prefcharid, $_SESSION['prefaccid'], $_SESSION['userid']);
		$scp->execute();
		$result = $scp->get_result();
		if ($row = $result->fetch_assoc()) {
			$uap = $con->prepare("UPDATE userinfo SET prefcharid = ?, prefcharname = ? WHERE userid = ?");
			$uap->bind_param("isi", $prefcharid, $row['charname'], $_SESSION['userid']);
			$uap->execute();
			$uap->close();
			$_SESSION['prefcharid'] = $row['charid'];
			$_SESSION['prefcharname'] = $row['charname'];
			$_SESSION['charprofid'] = $row['profid'];
		} else {
			echo 'Character preference not found.<br />';
		}
		$scp->close();
	}
}
?>