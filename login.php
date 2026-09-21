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
if ($con->connect_errno > 0){
	die ('Unable to connect to database [' . $con->connect_errno . ']');
}
if (session_status() == PHP_SESSION_NONE) {
	ini_set('session.use_strict_mode', '1');
	ini_set('session.use_only_cookies', '1');
	ini_set('session.cookie_httponly', '1');
	ini_set('session.cookie_samesite', 'Lax');
	if (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') {
		ini_set('session.cookie_secure', '1');
	}
	session_start();
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST' || !isset($_POST['username'], $_POST['password'])) {
	http_response_code(400);
	echo 'Invalid login request.<br />Please <a href="index.php" class="navlink">try again</a><br />';
	exit();
}

$username = $_POST['username'];
$password = $_POST['password'];

$stmt = $con->prepare("SELECT userid, username, userpass, usermail, admin, prefaccid, prefaccname, prefcharid, prefcharname FROM userinfo WHERE username = ? LIMIT 1");
$stmt->bind_param("s", $username);
$stmt->execute();
$result = $stmt->get_result();
$row = $result->fetch_assoc();
$stmt->close();

if ($row && password_verify($password, $row['userpass'])) {
	session_regenerate_id(true);
    $_SESSION['userid'] = $row['userid'];
    $_SESSION['username'] = $row['username'];
	$_SESSION['usermail'] = $row['usermail'];
	$_SESSION['admin'] = $row['admin'];
	$_SESSION['prefaccid'] = $row['prefaccid'];
	$_SESSION['prefaccname'] = $row['prefaccname'];
	$_SESSION['prefcharid'] = $row['prefcharid'];
	$_SESSION['prefcharname'] = $row['prefcharname'];
	$_SESSION['login_time'] = time();
	$_SESSION['last_activity'] = time();
    header("Location: index.php");
	exit();
} else {
    echo 'The username or password provided don\'t match!<br />Please <a href="index.php" class="navlink">try again</a><br />';
    exit();
}
?>