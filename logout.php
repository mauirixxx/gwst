<?php
$pagetitle = "Logging Out";
include_once ('header.php');
if (isset($_GET['action'])) {
	$logout = $_GET['action'];
} else {
	$logout = $_POST['action'];
}
if ($logout == "logout"){
	session_unset();
	session_destroy();
	header("refresh:2;url=index.php");
	echo '<CENTER>You have been logged out ...<BR />Returning to login screen in a few seconds</CENTER>';
} else {
	echo '<CENTER>Something went wrong, you haven\'t been logged out!<BR /><BR />Please click <A HREF="logout.php" CLASS="navlink">HERE</A> to try again</CENTER>';
}
?>