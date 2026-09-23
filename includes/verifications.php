<?php
function registrationError($message) {
    echo '<main class="auth-shell"><section class="auth-card">';
    echo '<div class="auth-brand"><div class="auth-brand-mark">GWTTT</div><div class="auth-brand-name">Guild Wars Titles &amp; Treasures Tracker</div></div>';
    echo '<h1>Registration problem</h1>';
    echo '<p class="auth-intro">' . h($message) . '</p>';
    echo '<div class="auth-links"><a href="register.php">Please try again</a></div>';
    echo '</section></main>';
    echo '</body></html>';
    exit();
}

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
        registrationError('This e-mail address is already registered.');
    }
}

function validateUsername($con, $uname) {
    if (strlen($uname) < 1 || strlen($uname) > 30 || !preg_match('/^[A-Za-z0-9]+$/', $uname)) {
        registrationError('Username must be 1-30 characters and contain letters and numbers only, with no spaces.');
    }

    $stmt = $con->prepare("SELECT 1 FROM userinfo WHERE username = ? LIMIT 1");
    $stmt->bind_param("s", $uname);
    $stmt->execute();
    $result = $stmt->get_result();
    $exists = $result->num_rows > 0;
    $stmt->close();

    if ($exists) {
        registrationError('This username has already been taken. Please choose another one.');
    }
}

if (!empty($_POST['username'])) {
    $username = trim($_POST['username']);
    validateUsername($con, $username);
}

if (!empty($_POST['useremail'])) {
    $email = trim($_POST['useremail']);
    if (strlen($email) > 50) {
        registrationError('E-mail address must be 50 characters or fewer.');
    }

    $verifyemail = validateEmail($email);
    if ($verifyemail === false) {
        registrationError('Please enter a valid e-mail address.');
    }
    $exclude_userid = isset($_SESSION['userid']) ? (int)$_SESSION['userid'] : null;
    usedEmail($con, $verifyemail, $exclude_userid);
}

if (isset($_POST['userpass1'], $_POST['userpass2'])) {
    $passwordLength = strlen($_POST['userpass1']);
    if ($passwordLength < 8) {
        registrationError('Password must be at least 8 characters long.');
    }
    if ($passwordLength > 255) {
        registrationError('Password must be 255 characters or fewer.');
    }
    if ($_POST['userpass1'] !== $_POST['userpass2']) {
        registrationError('Passwords do not match.');
    }
}
?>
