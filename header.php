<!DOCTYPE html>
<html>
<head>
<link rel="stylesheet" type="text/css" href="style.css">
<?php
if (session_status() == PHP_SESSION_NONE) {
	session_start();
}
$userid = (isset($_SESSION['userid']) ? $_SESSION['userid'] : null);
include_once ('connect.php');
$con = mysqli_connect(DATABASE_HOST, DATABASE_USER, DATABASE_PASS, DATABASE_NAME);
if ($con->connect_errno){
	die ('Unable to connect to database [' . $db->connect_errno . ']');
}
if (!$userid){
	echo '<title>Please login first</title></head><body><center>Aloha, and welcome to my Guild Wars stats tracker. Please login below.<hr>';
	echo '<form action="login.php" method="post"><table border="0"><tr><td>Username:</td><td><input type="text" name="username" size="20" autofocus required></td></tr>';
	echo '<tr><td>Password:</td><td><input type="password" name="password" size="20" required></td></tr></table>';
	echo '<input type="submit" value="Login ..."></form><br /><br />';
	echo 'If you haven\'t registered an account yet,<br />please click <a href="register.php" class="navlink">here</a> to create one.<br />';
} else {
	echo '<title>';
	if (isset($pagetitle)) {
		echo $pagetitle;
	} else {
		echo 'GWST';
	}
	echo '</title></head><body><center>';
	if (!empty($_POST['prefaccid'])) {
		//this section contains code to set the users preferred game account
		include_once ('includes/set-prefacc.php');
	}
	if (!empty($_POST['prefcharid'])) {
        //this section contains code to the users preferred character
        include_once ('includes/set-prefchar.php');
    }
	echo '<form action="' . $_SERVER['REQUEST_URI'] . '" method="post">';
	echo '(<a href="index.php" class="navlink">Home</a>) (<a href="preferences.php" class="navlink">Options</a>) ';
	if ($_SESSION['admin'] == 1){
		echo'(<a href="adminlanding.php" class="navlink">Administration</a>) ';
	}
	echo '(<select name="prefaccid" onchange="this.form.submit()">';
	echo '<option class="header" value="' . $_SESSION['prefaccid'] . '">' . $_SESSION['prefaccname'] . '</option>';
	echo '<option value="nopref">No default selected</option>';
	include_once ('header-list-accounts.php');
	echo '</select>)<noscript><input type="submit" value="Preferrence"></noscript> ';
	echo '(<select name="prefcharid" onchange="this.form.submit()">';
	echo '<option "class="header" value="' . $_SESSION['prefcharid'] . '">' . $_SESSION['prefcharname'] . '</option>';
	echo '<option value="nopref">No default selected</option>';
	include_once ('header-list-chars.php');
	echo '</select>)<noscript><input type="submit" value="Preferrence"></noscript> (<a href="logout.php?action=logout" class="navlink">Logout ' . $_SESSION['username'] . '</a>)</form><hr><br / >';
}
?>