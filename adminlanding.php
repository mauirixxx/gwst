<?php
$pagetitle = "Admin Area";
include_once ('header.php');

if (!isset($_SESSION['userid']) || !isset($_SESSION['admin']) || $_SESSION['admin'] != 1) {
	http_response_code(403);
	echo '<center>Access denied.</center>';
	include_once ('footer.php');
	exit();
}

unset($_SESSION['title']);
unset($_SESSION['tid']);
echo '<center>Welcome to the admin area!<br /><br />';
echo 'Title creator / editor <a href="titlemanager.php" class="navlink">here</a><br /><br />';
echo 'E-mail server settings <a href="mailsettings.php" class="navlink">here</a><br /><br />';
echo 'User editor <a href="usermanager.php" class="navlink">here</a><br /><br />';

include_once ('footer.php');
?>