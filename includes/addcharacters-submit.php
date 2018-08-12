<?php
if (isset($_SESSION['userid'])){
    // $pc = ProfessionColor
    $pc = $con->prepare("SELECT profcolor FROM gwprofessions WHERE profid = ?");
    $pc->bind_param("i", $_POST['profid']);
    $pc->execute();
    $profcolor = $pc->get_result()->fetch_object()->profcolor;
	// $ac = AddCharacter
	$ac = $con->prepare("INSERT INTO gwchars (accid, userid, charname, birthdate, profid, profcolor) VALUES (?, ?, ?, ?, ?, ?)");
	$ac->bind_param("iissis", $_SESSION['prefaccid'], $_SESSION['userid'], $_POST['newcharname'], $_POST['bdate'], $_POST['profid'], $profcolor);
	$ac->execute();
	$ac->close();
	echo $_POST['newcharname'] . ' added to your account!<br /><br />';
}
?>