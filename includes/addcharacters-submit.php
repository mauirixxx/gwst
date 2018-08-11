<?php

# delete this block when shit finally works.
ini_set('display_errors', 'on');
error_reporting(E_ALL);
mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);
# delete the above when shit finally works

if (isset($_SESSION['userid'])){
	// $ac = AddCharacter
	$ac = $con->prepare("INSERT INTO gwchars (accid, userid, charname, birthdate, profid) VALUES (?, ?, ?, ?, ?)");
	$ac->bind_param("iissi", $_SESSION['prefaccid'], $_SESSION['userid'], $_POST['newcharname'], $_POST['bdate'], $_POST['profid']);
	$ac->execute();
	$ac->close();
	echo $_POST['newcharname'] . ' added to your account!<br /><br />';
}
?>