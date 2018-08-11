<?php
if (isset($_SESSION['userid'])) {
    if ($_POST['prefcharid'] == "nopref") {
        $ncp = $con->prepare("UPDATE userinfo SET prefcharid = 0, prefcharname = 'No default selected' WHERE userid = ?");
		$ncp->bind_param("i", $_SESSION['userid']);
		$ncp->execute();
		$ncp->close();
		$_SESSION['prefcharid'] = "0";
		$_SESSION['prefcharname'] = "No default selected";
		echo 'Character preference update - no preferred character selected.<br />';
	} else {
		$scp = $con->prepare("SELECT charid, charname FROM gwchars WHERE charid = ? AND accid = ? AND userid = ?");
		$scp->bind_param("iii", $_POST['prefcharid'], $_SESSION['prefaccid'], $_SESSION['userid']);
		$scp->execute();
		$result = $scp->get_result();
		while ($row = $result->fetch_assoc()) {
			$uap = $con->prepare("UPDATE userinfo SET prefcharid = ?, prefcharname = ? WHERE userid = ?");
			$uap->bind_param("isi", $_POST['prefcharid'], $row['charname'], $_SESSION['userid']);
			$uap->execute();
			$uap->close();
			$_SESSION['prefcharid'] = $row['charid'];
			$_SESSION['prefcharname'] = $row['charname'];
		}
		echo 'Guild Wars preferred character updated! <br />';
	}
}
?>