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
	$username = htmlspecialchars($_SESSION['username'] ?? 'GWST user', ENT_QUOTES, 'UTF-8');
	?>
<!DOCTYPE html>
<html>
<head>
<meta name="viewport" content="width=device-width, initial-scale=1">
<meta charset="utf-8">
<title>Confirm logout - GWTTT</title>
<link rel="stylesheet" type="text/css" href="style.css?v=20260923-1">
<style>
.logout-page { min-height: 100vh; display: grid; place-items: center; padding: 28px; background: #0d1a22; }
.logout-card { width: min(100%, 560px); padding: 34px; border: 1px solid #2d6978; border-radius: 9px; background: #122936; box-shadow: 0 16px 38px rgba(0,0,0,.28); text-align: center; }
.logout-brand { margin-bottom: 26px; }
.logout-brand a { text-decoration: none; }
.logout-brand-mark { display: block; color: #f3e3bd; font: 700 38px/1 Georgia, serif; letter-spacing: .08em; text-shadow: 0 2px 5px #000; }
.logout-brand-name { display: block; margin-top: 8px; color: #9fb9c4; font-size: 12px; text-transform: uppercase; letter-spacing: .16em; }
.logout-card h1 { margin: 0 0 12px; color: #eef5f7; font-size: 30px; }
.logout-card p { margin: 0 0 26px; color: #b9d3df; font-size: 17px; line-height: 1.5; }
.logout-actions { display: flex; justify-content: center; flex-wrap: wrap; gap: 12px; }
.logout-actions form { margin: 0; }
.logout-button { display: inline-flex; align-items: center; justify-content: center; min-height: 44px; padding: 0 20px; border: 1px solid #2999a5; border-radius: 5px; background: #174454; color: #fff; font: 600 16px "Segoe UI", Tahoma, Arial, sans-serif; text-decoration: none; cursor: pointer; }
.logout-button:hover { background: #1b5668; }
.logout-cancel { border-color: #526b77; background: #243943; }
.logout-cancel:hover { background: #304a56; }
</style>
</head>
<body>
<main class="logout-page">
	<section class="logout-card">
		<div class="logout-brand">
			<a href="index.php">
				<span class="logout-brand-mark">GWTTT</span>
				<span class="logout-brand-name">Guild Wars Titles &amp; Treasures Tracker</span>
			</a>
		</div>
		<h1>Ready to log out?</h1>
		<p>You are currently signed in as <strong><?php echo $username; ?></strong>.</p>
		<div class="logout-actions">
			<form method="post" action="logout.php">
				<input type="hidden" name="action" value="logout">
				<input type="hidden" name="csrf_token" value="<?php echo $token; ?>">
				<button class="logout-button" type="submit">Confirm logout</button>
			</form>
			<a class="logout-button logout-cancel" href="index.php">Cancel</a>
		</div>
	</section>
</main>
</body>
</html>
	<?php
	exit();
}

http_response_code(400);
?>
<!DOCTYPE html>
<html>
<head>
<meta name="viewport" content="width=device-width, initial-scale=1">
<meta charset="utf-8">
<title>Invalid logout request - GWTTT</title>
<link rel="stylesheet" type="text/css" href="style.css?v=20260923-1">
</head>
<body>
<main class="page-shell"><p>Invalid logout request. Please return to <a href="index.php" class="navlink">GWTTT</a>.</p></main>
</body>
</html>