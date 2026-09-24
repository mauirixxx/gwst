<!DOCTYPE html>
<html>
<head>
<meta name="viewport" content="width=device-width, initial-scale=1">
<link rel="stylesheet" type="text/css" href="style.css?v=20260921-9">
<link rel="stylesheet" type="text/css" href="legacy-layout.css?v=20260922-1">
<link rel="stylesheet" type="text/css" href="auth.css?v=20260922-1">
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

function gwst_destroy_session(): void
{
	$_SESSION = array();
	if (ini_get('session.use_cookies')) {
		$params = session_get_cookie_params();
		setcookie(session_name(), '', time() - 42000, $params['path'], $params['domain'], $params['secure'], $params['httponly']);
	}
	if (session_status() === PHP_SESSION_ACTIVE) {
		session_destroy();
	}
}

if (isset($_SESSION['userid'])) {
	$now = time();
	$session_expired = false;
	if (isset($_SESSION['last_activity']) && ($now - $_SESSION['last_activity']) > $session_idle_timeout) $session_expired = true;
	if (isset($_SESSION['login_time']) && ($now - $_SESSION['login_time']) > $session_absolute_timeout) $session_expired = true;
	if ($session_expired) gwst_destroy_session();
	else $_SESSION['last_activity'] = $now;
}

$userid = (isset($_SESSION['userid']) ? (int)$_SESSION['userid'] : null);
if ($userid && $_SERVER['REQUEST_METHOD'] === 'POST') csrf_require_valid_post();

include_once ('connect.php');
$con = mysqli_connect(DATABASE_HOST, DATABASE_USER, DATABASE_PASS, DATABASE_NAME);
if ($con->connect_errno) die ('Unable to connect to database [' . $con->connect_errno . ']');

// Treat userinfo as authoritative on every authenticated request. This keeps
// admin/e-mail changes current and makes a password change invalidate all
// sessions created with the previous password hash.
if ($userid) {
	$identity = $con->prepare('SELECT username, usermail, userpass, admin, prefaccid, prefaccname, prefcharid, prefcharname FROM userinfo WHERE userid = ? LIMIT 1');
	$identity->bind_param('i', $userid);
	$identity->execute();
	$identityRow = $identity->get_result()->fetch_assoc();
	$identity->close();
	$currentFingerprint = $identityRow ? hash('sha256', (string)$identityRow['userpass']) : '';
	$sessionFingerprint = (string)($_SESSION['auth_password_fingerprint'] ?? '');
	if (!$identityRow || $sessionFingerprint === '' || !hash_equals($currentFingerprint, $sessionFingerprint)) {
		gwst_destroy_session();
		$userid = null;
	} else {
		$_SESSION['username'] = $identityRow['username'];
		$_SESSION['usermail'] = $identityRow['usermail'];
		$_SESSION['admin'] = $identityRow['admin'];
		$_SESSION['prefaccid'] = $identityRow['prefaccid'];
		$_SESSION['prefaccname'] = $identityRow['prefaccname'];
		$_SESSION['prefcharid'] = $identityRow['prefcharid'];
		$_SESSION['prefcharname'] = $identityRow['prefcharname'];
	}
}

if (!$userid){
	echo '<title>Please login first</title></head><body class="logged-out-body">';
	echo '<main class="auth-shell"><section class="auth-card">';
	echo '<div class="auth-brand"><div class="auth-brand-mark">GWTTT</div><div class="auth-brand-name">Guild Wars Titles &amp; Treasures Tracker</div></div>';
	echo '<h1>Welcome back</h1><p class="auth-intro">Sign in to manage your Guild Wars titles, characters, and treasures.</p>';
	echo '<form class="auth-form" action="login.php" method="post"><div class="auth-field"><label for="login-username">Username</label><input id="login-username" type="text" name="username" autocomplete="username" autofocus required></div><div class="auth-field"><label for="login-password">Password</label><input id="login-password" type="password" name="password" autocomplete="current-password" required></div><button class="auth-primary" type="submit">Log in</button></form>';
	echo '<div class="auth-links"><a href="forgot-password.php">Forgot your password?</a><span>New here? <a href="register.php">Create an account</a></span></div></section></main>';
} else {
	echo '<title>' . (isset($pagetitle) ? h($pagetitle) : 'GWTTT') . '</title></head><body>';
	$prefMessage = $_SESSION['preference_message'] ?? ''; unset($_SESSION['preference_message']);
	echo '<header class="site-header"><div class="brand"><a href="index.php"><span class="brand-mark">GWTTT</span><span class="brand-name">Guild Wars Titles &amp; Treasures Tracker</span></a></div><nav class="top-nav"><a class="nav-item" href="index.php">⌂ Home</a><a class="nav-item" href="preferences.php">⚙ Options</a>';
	echo '<form class="nav-select" action="includes/set-prefacc.php" method="post">' . csrf_input() . '<label for="header-account">Account</label><select id="header-account" name="prefaccid" onchange="this.form.submit()"><option value="0">No default selected</option>';
	$accountList=$con->prepare('SELECT accid, accemail FROM gwaccounts WHERE userid = ? ORDER BY accemail'); $accountList->bind_param('i',$_SESSION['userid']); $accountList->execute(); $accountResult=$accountList->get_result();
	while($account=$accountResult->fetch_assoc()){ $selected=((int)($_SESSION['prefaccid']??0)===(int)$account['accid'])?' selected':''; echo '<option value="'.(int)$account['accid'].'"'.$selected.'>'.h($account['accemail']).'</option>'; } $accountList->close(); echo '</select></form>';
	echo '<form class="nav-select" action="includes/set-prefchar.php" method="post">' . csrf_input() . '<label for="header-character">Character</label><select id="header-character" name="prefcharid" onchange="this.form.submit()"><option value="0">No default selected</option>';
	if(!empty($_SESSION['prefaccid'])){ $characterList=$con->prepare('SELECT charid,charname,profid FROM gwchars WHERE accid=? AND userid=? ORDER BY charname'); $characterList->bind_param('ii',$_SESSION['prefaccid'],$_SESSION['userid']); $characterList->execute(); $characterResult=$characterList->get_result(); while($character=$characterResult->fetch_assoc()){ $selected=((int)($_SESSION['prefcharid']??0)===(int)$character['charid'])?' selected':''; echo '<option class="profession-'.(int)$character['profid'].'" value="'.(int)$character['charid'].'"'.$selected.'>'.h($character['charname']).'</option>'; } $characterList->close(); }
	echo '</select></form>'; if(!empty($_SESSION['admin'])) echo '<a class="nav-item" href="adminlanding.php">⚒ Administration</a>'; echo '<a class="nav-item nav-logout" href="logout.php">↪ Logout '.h($_SESSION['username']).'</a></nav>';
	echo '<div class="action-nav">';
	echo '<a href="updateaccountstats.php"><strong>Update Account Titles</strong><small>Update progress for account titles</small></a>';
	echo '<a href="updatecharstats.php"><strong>Update Character Titles</strong><small>Update progress for character titles</small></a>';
	echo '<a href="treasures.php"><strong>Track Treasures</strong><small>Record treasure, gold, and loot drops</small></a>';
	echo '<span aria-disabled="true" style="display:flex;flex-direction:column;gap:3px;padding:14px 18px;border:1px solid #285367;border-radius:6px;background:linear-gradient(180deg,#173243,#122733);color:#dbe9ee;text-align:left;box-shadow:inset 0 1px rgba(255,255,255,.03);"><strong>This Space for Rent 😁</strong><small style="color:#9fb5bf;font-size:12px;">Prime GWTTT real estate</small></span>';
	echo '<a href="addaccounts.php"><strong>Manage Accounts &amp; Characters</strong><small>View and manage accounts and characters</small></a>';
	echo '<a href="miniatures.php"><strong>Track Miniatures</strong><small>Hall of Monuments dedication &amp; collection</small></a>';
	echo '</div></header>';
	$page_script=pathinfo($_SERVER['SCRIPT_NAME']??'',PATHINFO_FILENAME); $page_class=preg_replace('/[^a-zA-Z0-9_-]/','',$page_script); echo '<main class="page-shell page-'.h($page_class).'">'; if($prefMessage!=='') echo '<div class="preference-message">'.h($prefMessage).'</div>';
}
?>