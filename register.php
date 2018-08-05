<!DOCTYPE html>
<html>
<head>
<link rel="stylesheet" type="text/css" href="style.css">
<?php
session_start();
include_once ('connect.php');
$newuser = $_POST['reguser'];
$con = mysqli_connect(DATABASE_HOST, DATABASE_USER, DATABASE_PASS, DATABASE_NAME);
if ($con->connect_errno){
	die ('Unable to connect to database [' . $db->connect_errno . ']');
}
if (is_null($newuser)){
	// this block contains the code to fill out the form
	echo '<center>';
	include_once ('includes/account-registration.php');
	echo '</center><br />';
} else {
	include_once ('includes/verifications.php');
	// this block validates input, and if passed, inserts it into the database
	$hashedpass = password_hash($_POST['userpass1'], PASSWORD_DEFAULT);
	$stmt = $con->prepare("INSERT INTO userinfo (username, userpass, usermail) VALUES (?, ?, ?)");
	$stmt->bind_param("sss", $username, $hashedpass, $verifyemail);
	$stmt->execute();
	echo '<center>Account created, please <a href="index.php">login</a> to continue<br /><br />';
	session_destroy();
	exit();
}
echo '<center>Back to <a href="index.php" class="navlink">home page</a><br />';
include_once ('footer.php');
?>