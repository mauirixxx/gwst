<!DOCTYPE html>
<html>
<head>
<meta name="viewport" content="width=device-width, initial-scale=1">
<link rel="stylesheet" type="text/css" href="style.css">
<link rel="stylesheet" type="text/css" href="auth.css?v=20260923-1">
<title>Logging in</title>
</head>
<body class="logged-out-body">
<?php
include_once ('connect.php');
include_once (__DIR__ . '/includes/auth-security.php');
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

function login_error_page(string $message): void
{
    echo '<main class="auth-shell"><section class="auth-card">';
    echo '<div class="auth-brand"><div class="auth-brand-mark">GWTTT</div><div class="auth-brand-name">Guild Wars Titles &amp; Treasures Tracker</div></div>';
    echo '<h1>Sign in failed</h1>';
    echo '<div class="auth-error" role="alert">' . htmlspecialchars($message, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8') . '</div>';
    echo '<a class="auth-primary auth-primary-link" href="index.php">Try again</a>';
    echo '<div class="auth-links"><a href="forgot-password.php">Forgot your password?</a></div>';
    echo '</section></main>';
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST' || !isset($_POST['username'], $_POST['password'])) {
    http_response_code(400);
    login_error_page('Invalid login request.');
    exit();
}

$username = trim($_POST['username']);
$password = $_POST['password'];
$clientIp = gwst_client_ip();
$loginIpKey = gwst_throttle_key('login-ip', $clientIp);
$loginUserKey = gwst_throttle_key('login-user', $username);
gwst_throttle_cleanup($con);

if (gwst_throttle_is_blocked($con, 'login-ip', $loginIpKey)
    || gwst_throttle_is_blocked($con, 'login-user', $loginUserKey)) {
    gwst_rate_limited_response('index.php');
}

$stmt = $con->prepare("SELECT userid, username, userpass, usermail, admin, prefaccid, prefaccname, prefcharid, prefcharname FROM userinfo WHERE username = ? LIMIT 1");
$stmt->bind_param("s", $username);
$stmt->execute();
$result = $stmt->get_result();
$row = $result->fetch_assoc();
$stmt->close();

if ($row && password_verify($password, $row['userpass'])) {
    gwst_throttle_clear($con, 'login-user', $loginUserKey);
    session_regenerate_id(true);
    $_SESSION['userid'] = $row['userid'];
    $_SESSION['username'] = $row['username'];
    $_SESSION['usermail'] = $row['usermail'];
    $_SESSION['admin'] = $row['admin'];
    $_SESSION['prefaccid'] = $row['prefaccid'];
    $_SESSION['prefaccname'] = $row['prefaccname'];
    $_SESSION['prefcharid'] = $row['prefcharid'];
    $_SESSION['prefcharname'] = $row['prefcharname'];
    // A one-way fingerprint lets authenticated requests detect a later password
    // change without storing the password or requiring a schema migration.
    $_SESSION['auth_password_fingerprint'] = hash('sha256', (string)$row['userpass']);
    $_SESSION['login_time'] = time();
    $_SESSION['last_activity'] = time();
    header("Location: index.php");
    exit();
}

gwst_throttle_record_failure($con, 'login-user', $loginUserKey, GWST_LOGIN_USER_LIMIT);
gwst_throttle_record_failure($con, 'login-ip', $loginIpKey, GWST_LOGIN_IP_LIMIT);
http_response_code(401);
login_error_page('The username or password provided does not match.');
exit();
?>
</body>
</html>
