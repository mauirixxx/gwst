<!DOCTYPE html>
<html>
<head>
<meta name="viewport" content="width=device-width, initial-scale=1">
<title>Create account - GWTTT</title>
<link rel="stylesheet" type="text/css" href="style.css?v=20260921-9">
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

include_once (__DIR__ . '/includes/html.php');
include_once (__DIR__ . '/includes/csrf.php');
include_once ('connect.php');

$con = mysqli_connect(DATABASE_HOST, DATABASE_USER, DATABASE_PASS, DATABASE_NAME);
if ($con->connect_errno) {
    die('Unable to connect to database [' . $con->connect_errno . ']');
}
?>
</head>
<body class="logged-out-body">
<?php
if (!empty($_SESSION['userid'])) {
    echo '<main class="auth-shell"><section class="auth-card">';
    echo '<div class="auth-brand"><div class="auth-brand-mark">GWTTT</div><div class="auth-brand-name">Guild Wars Titles &amp; Treasures Tracker</div></div>';
    echo '<h1>Already signed in</h1>';
    echo '<p class="auth-intro">Log out first if you want to create another GWTTT account.</p>';
    echo '<div class="auth-links"><a href="index.php">Return home</a></div>';
    echo '</section></main></body></html>';
    exit();
}

if (empty($_POST['reguser'])) {
    echo '<main class="auth-shell">';
    echo '<section class="auth-card">';
    echo '<div class="auth-brand"><div class="auth-brand-mark">GWTTT</div><div class="auth-brand-name">Guild Wars Titles &amp; Treasures Tracker</div></div>';
    echo '<h1>Create your account</h1>';
    echo '<p class="auth-intro">Create a GWTTT login to track your Guild Wars accounts, characters, titles, and treasures.</p>';
    include_once ('includes/account-registration.php');
    echo '<div class="auth-links"><span>Already have an account? <a href="index.php">Log in</a></span></div>';
    echo '</section>';
    echo '</main>';
} else {
    csrf_require_valid_post();

    if (!isset($_POST['username'], $_POST['useremail'], $_POST['userpass1'], $_POST['userpass2'])
        || trim($_POST['username']) === ''
        || trim($_POST['useremail']) === ''
        || $_POST['userpass1'] === ''
        || $_POST['userpass2'] === '') {
        http_response_code(400);
        echo '<main class="auth-shell"><section class="auth-card">';
        echo '<div class="auth-brand"><div class="auth-brand-mark">GWTTT</div><div class="auth-brand-name">Guild Wars Titles &amp; Treasures Tracker</div></div>';
        echo '<h1>Registration incomplete</h1>';
        echo '<p class="auth-intro">All registration fields are required.</p>';
        echo '<div class="auth-links"><a href="register.php">Please try again</a></div>';
        echo '</section></main>';
        echo '</body></html>';
        exit();
    }

    include_once ('includes/verifications.php');
    $hashedpass = password_hash($_POST['userpass1'], PASSWORD_DEFAULT);
    $first_user_admin = false;

    // Serialize registration while deciding whether this is the first user.
    $con->begin_transaction();
    try {
        $lock = $con->prepare("LOCK TABLES userinfo WRITE");
        $lock->execute();
        $lock->close();

        $count = $con->prepare("SELECT COUNT(*) FROM userinfo");
        $count->execute();
        $count->bind_result($user_count);
        $count->fetch();
        $count->close();

        $admin = ((int)$user_count === 0) ? 1 : 0;
        $stmt = $con->prepare("INSERT INTO userinfo (username, userpass, usermail, admin) VALUES (?, ?, ?, ?)");
        $stmt->bind_param("sssi", $username, $hashedpass, $verifyemail, $admin);
        $stmt->execute();
        $stmt->close();

        $first_user_admin = ($admin === 1);

        $unlock = $con->prepare("UNLOCK TABLES");
        $unlock->execute();
        $unlock->close();
        $con->commit();
    } catch (Throwable $e) {
        $con->query("UNLOCK TABLES");
        $con->rollback();
        throw $e;
    }

    echo '<main class="auth-shell"><section class="auth-card">';
    echo '<div class="auth-brand"><div class="auth-brand-mark">GWTTT</div><div class="auth-brand-name">Guild Wars Titles &amp; Treasures Tracker</div></div>';
    echo '<h1>Account created!</h1>';
    echo '<p class="auth-intro">Your GWTTT account is ready.';
    if ($first_user_admin) {
        echo ' As the first GWTTT user, this account has been granted administrator access.';
    }
    echo '</p>';
    echo '<div class="auth-links"><a href="index.php">Log in to continue</a></div>';
    echo '</section></main>';

    $_SESSION = array();
    session_destroy();
}
?>
</body>
</html>
