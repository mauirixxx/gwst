<!DOCTYPE html>
<HTML>
<HEAD>
<link rel="stylesheet" type="text/css" href="style.css">
<?php
session_start();
$userid = (isset($_SESSION['userid']) ? $_SESSION['userid'] : null);
include_once ('connect.php');
$con = mysqli_connect(DATABASE_HOST, DATABASE_USER, DATABASE_PASS, DATABASE_NAME);
if ($con->connect_errno){
	die ('Unable to connect to database [' . $db->connect_errno . ']');
}
if (!$userid){
	echo '<title>Please login first</title></head><body><center>Aloha, and welcome to my Guild Wars stats tracker. Please login below.<hr>';
	echo '<FORM ACTION="login.php" METHOD="POST"><TABLE BORDER="0"><TR><TD>Username:</TD><TD><INPUT TYPE="TEXT" NAME="username" SIZE="20"></TD></TR>';
	echo '<tr><td>Password:</td><td><input type="password" name="password" SIZE="20"></td></tr></table>';
	echo '<input type="submit" value="Login ..."></form><br /><br />';
	echo 'If you haven\'t registered an account yet,<br />please click <a href="register.php" class="navlink">here</a> to create one.<br />';
} else {
	echo '<title>' . $pagetitle . '</title></head><body><center>';
	echo '(<a href="index.php" class="navlink">Home</a>) (<a href="preferences.php" class="navlink">Options</a>) ';
	if ($_SESSION['admin'] == 1){
		echo'(<a href="adminlanding.php" class="navlink">Administration</a>) ';
	}
	echo '(<a href="logout.php?action=logout" class="navlink">Logout ' . $_SESSION['username'] . '</a>)<hr><br / >';
}
?>