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
include_once (__DIR__ . '/includes/html.php');
include_once (__DIR__ . '/includes/csrf.php');
include_once ('connect.php');
$con = mysqli_connect(DATABASE_HOST, DATABASE_USER, DATABASE_PASS, DATABASE_NAME);
if ($con->connect_errno){
    die ('Unable to connect to database [' . $con->connect_errno . ']');
}

if (empty($_POST['reguser'])){
    echo '<center>';
    include_once ('includes/account-registration.php');
    echo '</center><br />';
} else {
    csrf_require_valid_post();

    if (!isset($_POST['username'], $_POST['useremail'], $_POST['userpass1'], $_POST['userpass2'])
        || trim($_POST['username']) === ''
        || trim($_POST['useremail']) === ''
        || $_POST['userpass1'] === ''
        || $_POST['userpass2'] === '') {
        http_response_code(400);
        echo '<center>All registration fields are required.<br /><a href="register.php" class="navlink">Please try again!</a><br />';
        exit();
    }

    include_once ('includes/verifications.php');
    $hashedpass = password_hash($_POST['userpass1'], PASSWORD_DEFAULT);
    $stmt = $con->prepare("INSERT INTO userinfo (username, userpass, usermail) VALUES (?, ?, ?)");
    $stmt->bind_param("sss", $username, $hashedpass, $verifyemail);
    $stmt->execute();
    $stmt->close();

    echo '<center>Account created, please <a href="index.php">login</a> to continue<br /><br />';
    $_SESSION = array();
    session_destroy();
    exit();
}
echo '<center>Back to <a href="index.php" class="navlink">home page</a><br />';
include_once ('footer.php');
?>