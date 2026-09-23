<?php
$pagetitle = 'Create account';
include_once ('header.php');

// header.php renders the normal logged-out login card, so registration uses
// its own standalone auth shell instead of continuing that output.
if (empty($_SESSION['userid'])) {
    echo '</section></main>';
}

if (!empty($_SESSION['userid'])) {
    echo '<section class="auth-shell"><div class="auth-card">';
    echo '<h1>Create account</h1>';
    echo '<p class="auth-intro">You are already signed in. Log out first if you want to create another GWTTT account.</p>';
    echo '<div class="auth-links"><a href="index.php">Return home</a></div>';
    echo '</div></section>';
    include_once ('footer.php');
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
        echo '<h1>Registration incomplete</h1>';
        echo '<p class="auth-intro">All registration fields are required.</p>';
        echo '<div class="auth-links"><a href="register.php">Please try again</a></div>';
        echo '</section></main>';
        include_once ('footer.php');
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
        // UNLOCK TABLES implicitly releases the table lock if one was acquired.
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

include_once ('footer.php');
?>