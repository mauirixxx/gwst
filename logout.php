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

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'logout') {
	csrf_require_valid_post();
	$_SESSION = array();

	if (ini_get("session.use_cookies")) {
		$params = session_get_cookie_params();
		setcookie(session_name(), '', time() - 42000, $params["path"], $params["domain"], $params["secure"], $params["httponly"]);
	}

	session_destroy();
	header("Location: index.php");
	exit();
}

if ($_SERVER['REQUEST_METHOD'] === 'GET') {
	$token = htmlspecialchars(csrf_token(), ENT_QUOTES, 'UTF-8');
	echo '<CENTER><form method="post" action="logout.php"><input type="hidden" name="action" value="logout"><input type="hidden" name="csrf_token" value="' . $token . '"><input type="submit" value="Confirm logout"></form></CENTER>';
	exit();
}

http_response_code(400);
echo '<CENTER>Invalid logout request.<BR /><BR />Please return to <A HREF="index.php" CLASS="navlink">GWST</A>.</CENTER>';
?>