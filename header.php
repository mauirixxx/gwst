<!DOCTYPE html>
<html>
<head>
<meta name="viewport" content="width=device-width, initial-scale=1">
<link rel="stylesheet" type="text/css" href="style.css?v=20260921-3">
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
	echo '<input type="submit" value="Login ..."></form><br />';
	echo '<a href="forgot-password.php" class="navlink">Forgot your password?</a><br /><br />';
	echo 'If you haven\'t registered an account yet,<br />please click <a href="register.php" class="navlink">here</a> to create one.<br />';
} else {
	echo '<title>';
	if (isset($pagetitle)) {
		echo h($pagetitle);
	} else {
		echo 'GWST';
	}
	echo '</title></head><body>';
	if (!empty($_POST['prefaccid'])) {
		include_once ('includes/set-prefacc.php');
	}
	if (!empty($_POST['prefcharid'])) {
        include_once ('includes/set-prefchar.php');
    }

	include_once ('header-list-accounts.php');
	include_once ('header-list-chars.php');

	echo '<header class="site-header">';
	echo '<div class="brand-row"><a href="index.php" class="brand-mark">GWST</a><span class="brand-subtitle">Guild Wars Stats Tracker</span></div>';
	echo '<nav class="main-nav">';
	echo '<a href="index.php" class="nav-item nav-home">⌂ <span>Home</span></a>';
	echo '<a href="preferences.php" class="nav-item">⚙ <span>Options</span></a>';

	echo '<form method="post" action="index.php" class="nav-select-form">';
	echo csrf_input();
	echo '<label for="prefaccid">Account</label>';
	echo '<select id="prefaccid" name="prefaccid" onchange="this.form.submit()">';
	echo '<option value="0">No default selected</option>';
	foreach ($header_accounts as $account) {
		$selected = ((int) $_SESSION['prefaccid'] === (int) $account['accid']) ? ' selected' : '';
		echo '<option value="' . (int) $account['accid'] . '"' . $selected . '>' . h($account['accemail']) . '</option>';
	}
	echo '</select></form>';

	echo '<form method="post" action="index.php" class="nav-select-form">';
	echo csrf_input();
	echo '<label for="prefcharid">Character</label>';
	echo '<select id="prefcharid" name="prefcharid" onchange="this.form.submit()">';
	echo '<option value="0">No default selected</option>';
	foreach ($header_characters as $character) {
		$selected = ((int) $_SESSION['prefcharid'] === (int) $character['charid']) ? ' selected' : '';
		echo '<option value="' . (int) $character['charid'] . '"' . $selected . '>' . h($character['charname']) . '</option>';
	}
	echo '</select></form>';

	if (!empty($_SESSION['admin'])) {
		echo '<a href="adminlanding.php" class="nav-item">⚒ <span>Administration</span></a>';
	}
	echo '<a href="logout.php" class="nav-item">↪ <span>Logout <strong>' . h($_SESSION['username']) . '</strong></span></a>';
	echo '</nav>';

	echo '<div class="action-row">';
	echo '<a href="updateaccountstats.php" class="action-card"><strong>Update Account Titles</strong><span>Update progress for account titles</span></a>';
	echo '<a href="updatecharstats.php" class="action-card"><strong>Update Character Titles</strong><span>Update progress for character titles</span></a>';
	echo '<a href="addaccounts.php" class="action-card"><strong>Manage Accounts &amp; Characters</strong><span>View and manage accounts and characters</span></a>';
	echo '</div>';
	echo '</header>';
}
?>