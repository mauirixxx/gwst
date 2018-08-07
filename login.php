<!DOCTYPE html>
<HTML>
<HEAD>
<link rel="stylesheet" type="text/css" href="style.css">
<TITLE>Logging in</TITLE>
</HEAD>
<BODY>
<CENTER>
<?php
include_once ('connect.php');
$con = mysqli_connect(DATABASE_HOST, DATABASE_USER, DATABASE_PASS, DATABASE_NAME);
if (session_status() == PHP_SESSION_NONE) {
	session_start();
}
$username = mysqli_real_escape_string($con, $_POST['username']);
$password = $_POST['password'];

if ($con->connect_errno > 0){
	die ('Unable to connect to database [' . $db->connect_errno . ']');
}
$sqluname = "select * from userinfo where username = '$username'";
$result = mysqli_query($con, $sqluname);
$row = mysqli_fetch_row ($result);
$verifypass = password_verify ($password,$row[2]);
if ($verifypass) {
    $_SESSION['userid'] = $row[0];
    $_SESSION['username'] = $row[1];
	$_SESSION['usermail'] = $row[3];
	$_SESSION['admin'] = $row[4];
    include_once ('header.php');
    header("refresh:1;url=index.php");
    echo '<center>You have successfully logged in!<br />';
} else {
    echo 'The username or password provided don\'t match!<br />Please <a href="index.php" class="navlink">try again</a><br />';
    exit();
}
?>