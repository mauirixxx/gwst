<!DOCTYPE html>
<html>
<head>
<link rel="stylesheet" type="text/css" href="style.css">
<?php
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

include_once (__DIR__ . '/includes/csrf.php');
include_once (__DIR__ . '/includes/html.php');

$session_idle_timeout = 1800;
$session_absolute_timeout = 28800;

if (isset($_SESSION['userid'])) {
	$now = time();
	$session_expired = false;

	if (isset($_SESSION['last_activity']) && ($now - $_SESSION['last_activity']) > $session_idle_timeout) {
		$session_expired = true;
	}
	if (isset($_SESSION['login_time']) && ($now - $_SESSION['login_time']) > $session_absolute_timeout) {
		$session_expired = true;
	}

	if ($session_expired) {
		$_SESSION = array();
		if (ini_get("session.use_cookies")) {
			$params = session_get_cookie_params();
			setcookie(session_name(), '', time() - 42000, $params["path"], $params["domain"], $params["secure"], $params["httponly"]);
		}
		session_destroy();
	} else {
		$_SESSION['last_activity'] = $now;
	}
}

$userid = (isset($_SESSION['userid']) ? $_SESSION['userid'] : null);

if ($userid && $_SERVER['REQUEST_METHOD'] === 'POST') {
	csrf_require_valid_post();
}
include_once ('connect.php');
$con = mysqli_connect(DATABASE_HOST, DATABASE_USER, DATABASE_PASS, DATABASE_NAME);
if ($con->connect_errno){
	die ('Unable to connect to database [' . $con->connect_errno . ']');
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
		echo h($pagetitle);
	} else {
		echo 'GWST';
	}
	echo '</title></head><body><center>';
	if (!empty($_POST['prefaccid'])) {
		include_once ('includes/set-prefacc.php');
	}
	if (!empty($_POST['prefcharid'])) {
        include_once ('includes/set-prefchar.php');
    }
	echo '<form action="' . h($_SERVER['REQUEST_URI']) . '" method="post">';
	echo '(<a href="index.php" class="navlink">Home</a>) (<a href="preferences.php" class="navlink">Options</a>) ';
	echo '(<select name="prefaccid" onchange="this.form.submit()">';
	echo '<option class="header" value="' . $_SESSION['prefaccid'] . '">' . h($_SESSION['prefaccname']) . '</option>';
	echo '<option value="nopref">No default selected</option>';
	include_once ('header-list-accounts.php');
	echo '</select>)<noscript><input type="submit" value="Preferrence"></noscript> ';
	echo '(<select name="prefcharid" onchange="this.form.submit()">';
	echo '<option "class="header" value="' . (int)$_SESSION['prefcharid'] . '">' . h($_SESSION['prefcharname']) . '</option>';
	echo '<option value="nopref">No default selected</option>';
	include_once ('header-list-chars.php');
	echo '</select>)<noscript><input type="submit" value="Preferrence"></noscript> ';
	if ($_SESSION['admin'] == 1){
		echo'(<a href="adminlanding.php" class="navlink">Administration</a>) ';
	}
	echo '(<a href="logout.php?action=logout" class="navlink">Logout ' . h($_SESSION['username']) . '</a>)</form><hr>';
	echo '(<a href="updateaccountstats.php" class="navlink">Update account titles</a>) (<a href="updatecharstats.php" class="navlink">Update character titles</a>) ';
	echo '(<a href="addaccounts.php" class="navlink">Manage accounts & characters</a>)<br /><br />';
}
?>