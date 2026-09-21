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

if (isset($_GET['action']) && $_GET['action'] === 'logout') {
	$_SESSION = array();

	if (ini_get("session.use_cookies")) {
		$params = session_get_cookie_params();
		setcookie(session_name(), '', time() - 42000, $params["path"], $params["domain"], $params["secure"], $params["httponly"]);
	}

	session_destroy();
	header("Location: index.php");
	exit();
}

http_response_code(400);
echo '<CENTER>Something went wrong, you haven\'t been logged out!<BR /><BR />Please return to <A HREF="index.php" CLASS="navlink">GWST</A>.</CENTER>';
?>