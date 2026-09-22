<!DOCTYPE html>
<html>
<head>
<meta name="viewport" content="width=device-width, initial-scale=1">
<link rel="stylesheet" type="text/css" href="style.css?v=20260921-9">
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
	echo '<title>Please login first</title></head><body><center>Aloha, and welcome to my Guild Wars Titles &amp; Treasures Tracker. Please login below.<hr>';
	echo '<form action="login.php" method="post"><table border="0"><tr><td>Username:</td><td><input type="text" name="username" size="20" autofocus required></td></tr>';
	echo '<tr><td>Password:</td><td><input type="password" name="password" size="20" required></td></tr></table>';
	echo '<input type="submit" value="Login ..."></form><br />';
	echo '<a href="forgot-password.php" class="navlink" style="color:#fff2a8;">Forgot your password?</a><br /><br />';
	echo 'If you haven\'t registered an account yet,<br />please click <a href="register.php" class="navlink" style="color:#fff2a8;">here</a> to create one.<br />';
} else {
	echo '<title>';
	if (isset($pagetitle)) {
		echo h($pagetitle);
	} else {
		echo 'GWTTT';
	}
	echo '</title></head><body>';

	$prefMessage = $_SESSION['preference_message'] ?? '';
	unset($_SESSION['preference_message']);

	echo '<header class="site-header">';
	echo '<div class="brand"><a href="index.php"><span class="brand-mark">GWTTT</span><span class="brand-name">Guild Wars Titles &amp; Treasures Tracker</span></a></div>';
	echo '<nav class="top-nav">';
	echo '<a class="nav-item" href="index.php">⌂ Home</a>';
	echo '<a class="nav-item" href="preferences.php">⚙ Options</a>';

	echo '<form class="nav-select" action="includes/set-prefacc.php" method="post">';
	echo csrf_input();
	echo '<label for="header-account">Account</label>';
	echo '<select id="header-account" name="prefaccid" onchange="this.form.submit()">';
	echo '<option value="0">No default selected</option>';
	$accountList = $con->prepare('SELECT accid, accemail FROM gwaccounts WHERE userid = ? ORDER BY accemail');
	$accountList->bind_param('i', $_SESSION['userid']);
	$accountList->execute();
	$accountResult = $accountList->get_result();
	while ($account = $accountResult->fetch_assoc()) {
		$selected = ((int) ($_SESSION['prefaccid'] ?? 0) === (int) $account['accid']) ? ' selected' : '';
		echo '<option value="' . (int) $account['accid'] . '"' . $selected . '>' . h($account['accemail']) . '</option>';
	}
	$accountList->close();
	echo '</select></form>';

	echo '<form class="nav-select" action="includes/set-prefchar.php" method="post">';
	echo csrf_input();
	echo '<label for="header-character">Character</label>';
	echo '<select id="header-character" name="prefcharid" onchange="this.form.submit()">';
	echo '<option value="0">No default selected</option>';
	if (!empty($_SESSION['prefaccid'])) {
		$characterList = $con->prepare('SELECT charid, charname FROM gwchars WHERE accid = ? AND userid = ? ORDER BY charname');
		$characterList->bind_param('ii', $_SESSION['prefaccid'], $_SESSION['userid']);
		$characterList->execute();
		$characterResult = $characterList->get_result();
		while ($character = $characterResult->fetch_assoc()) {
			$selected = ((int) ($_SESSION['prefcharid'] ?? 0) === (int) $character['charid']) ? ' selected' : '';
			echo '<option value="' . (int) $character['charid'] . '"' . $selected . '>' . h($character['charname']) . '</option>';
		}
		$characterList->close();
	}
	echo '</select></form>';

	if (!empty($_SESSION['admin'])) {
		echo '<a class="nav-item" href="adminlanding.php">⚒ Administration</a>';
	}
	echo '<a class="nav-item nav-logout" href="logout.php">↪ Logout ' . h($_SESSION['username']) . '</a>';
	echo '</nav>';

	echo '<div class="action-nav">';
	echo '<a href="updateaccountstats.php"><strong>Update Account Titles</strong><small>Update progress for account titles</small></a>';
	echo '<a href="updatecharstats.php"><strong>Update Character Titles</strong><small>Update progress for character titles</small></a>';
	echo '<a href="treasures.php"><strong>Track Treasures</strong><small>Record treasure, gold, and loot drops</small></a>';
	echo '<a href="addaccounts.php"><strong>Manage Accounts &amp; Characters</strong><small>View and manage accounts and characters</small></a>';
	echo '</div>';
	echo '</header>';
	echo '<main class="page-shell">';
	if ($prefMessage !== '') {
		echo '<div class="preference-message">' . h($prefMessage) . '</div>';
	}
}
?>