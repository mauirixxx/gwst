<?php
function validateEmail($email) {
    return filter_var($email, FILTER_VALIDATE_EMAIL);
}

function usedEmail($con, $usedemail, $exclude_userid = null) {
    if ($exclude_userid === null) {
        $stmt = $con->prepare("SELECT 1 FROM userinfo WHERE usermail = ? LIMIT 1");
        $stmt->bind_param("s", $usedemail);
    } else {
        $stmt = $con->prepare("SELECT 1 FROM userinfo WHERE usermail = ? AND userid <> ? LIMIT 1");
        $stmt->bind_param("si", $usedemail, $exclude_userid);
    }
    $stmt->execute();
    $result = $stmt->get_result();
    $exists = $result->num_rows > 0;
    $stmt->close();

    if ($exists) {
        echo '<hr><center>This e-mail address is already registered.<br /><a href="register.php" class="navlink">Please try again!</a><hr><br />';
        include_once (__DIR__ . '/../footer.php');
        exit();
    }
}

function validateUsername($con, $uname) {
    $stmt = $con->prepare("SELECT 1 FROM userinfo WHERE username = ? LIMIT 1");
    $stmt->bind_param("s", $uname);
    $stmt->execute();
    $result = $stmt->get_result();
    $exists = $result->num_rows > 0;
    $stmt->close();

    if ($exists) {
        echo '<center>This username has already been taken, please choose another one<br /><a href="register.php" class="navlink">Please try again!</a><br />';
        include_once (__DIR__ . '/../footer.php');
        exit();
    }
}

if (!empty($_POST['username'])) {
    $username = trim($_POST['username']);
    validateUsername($con, $username);
}

if (!empty($_POST['useremail'])) {
    $verifyemail = validateEmail(trim($_POST['useremail']));
    if ($verifyemail === false) {
        echo '<center>This address: ' . h($_POST['useremail']) . ' is not a valid e-mail address!<br />Please verify and type it again.<br />';
        include_once (__DIR__ . '/../footer.php');
        exit();
    }
    $exclude_userid = isset($_SESSION['userid']) ? (int)$_SESSION['userid'] : null;
    usedEmail($con, $verifyemail, $exclude_userid);
}

if (isset($_POST['userpass1'], $_POST['userpass2']) && $_POST['userpass1'] !== $_POST['userpass2']) {
    echo '<center>The passwords don\'t match!<br />Please try again!';
    include_once (__DIR__ . '/../footer.php');
    exit();
}
?>