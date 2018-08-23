<?php
if (isset($_SESSION['userid'])) {
	include_once ('verifications.php');
	$verifypass = $con->prepare("SELECT userpass FROM userinfo WHERE userid = ?");
	$verifypass->bind_param("i", $_SESSION['userid']);
	$verifypass->execute();
	$result = $verifypass->get_result();
	while ($row = $result->fetch_assoc()) {
		$vp = password_verify ($_POST['oldpass'],$row['userpass']);
		if ($vp) {
			$hp = password_hash($_POST['userpass1'], PASSWORD_DEFAULT);
			echo 'Verified old password, updating to new password!<br />';
			$updpass = $con->prepare("UPDATE userinfo SET userpass = ? WHERE userid = ?");
			$updpass->bind_param("si", $hp, $_SESSION['userid']);
			$updpass->execute();
			echo 'Password updated!<br />';
			$updpass->close();
		} else {
			echo 'Old password doesn\'t match, password is NOT updated!<br />';
		}
	}
}
?>