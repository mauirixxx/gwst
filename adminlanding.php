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
echo 'Title creator / editor <a href="titlemanager.php" class="navlink">here</a> (work in progress)<br /><br />';
echo 'User editor <a href="" class="navlink">here</a> (not working yet)<br /><br />';
echo 'testing autofilled title theories: <a href="autofilled.php" class="navlink">Legendary title testGWAMM</a><br /><br />';

include_once ('footer.php');
?>