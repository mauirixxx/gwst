<?php
$pagetitle = "Admin Area";
include_once ('header.php');
if (isset($_SESSION['userid'])){
	unset($_SESSION['title']);
	unset($_SESSION['tid']);
	echo '<center>Welcome to the admin area!<br /><br />';
	echo 'Title creator / editor <a href="titlemanager.php" class="navlink">here</a> (work in progress)<br /><br />';
	echo 'User editor <a href="" class="navlink">here</a> (not working yet)<br /><br />';
	include_once ('includes/session-dump.php');
}
include_once ('footer.php');
?>