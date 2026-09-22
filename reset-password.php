<!DOCTYPE html>
<html>
<head>
<meta name="viewport" content="width=device-width, initial-scale=1">
<link rel="stylesheet" type="text/css" href="style.css?v=20260921-3">
<link rel="stylesheet" type="text/css" href="password-reset.css?v=20260921-1">
<title>Reset password - GWST</title>
</head>
<body class="password-reset-page">
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

require_once __DIR__ . '/connect.php';
require_once __DIR__ . '/includes/csrf.php';
require_once __DIR__ . '/includes/html.php';

$con = mysqli_connect(DATABASE_HOST, DATABASE_USER, DATABASE_PASS, DATABASE_NAME);
if ($con->connect_errno) {
    die('Unable to connect to database.');
}

$token = trim((string)($_POST['token'] ?? $_GET['token'] ?? ''));
$tokenHash = ($token !== '') ? hash('sha256', $token) : '';
$error = '';
$success = false;
$resetRow = null;

if ($tokenHash !== '') {
    $lookup = $con->prepare('SELECT token_id, userid FROM password_reset_tokens WHERE token_hash = ? AND used_at IS NULL AND expires_at > NOW() LIMIT 1');
    $lookup->bind_param('s', $tokenHash);
    $lookup->execute();
    $resetRow = $lookup->get_result()->fetch_assoc();
    $lookup->close();
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    csrf_require_valid_post();
    $password1 = (string)($_POST['password1'] ?? '');
    $password2 = (string)($_POST['password2'] ?? '');

    if (!$resetRow) {
        $error = 'This password reset link is invalid or has expired.';
    } elseif (strlen($password1) < 8) {
        $error = 'Your new password must be at least 8 characters long.';
    } elseif ($password1 !== $password2) {
        $error = 'The new passwords do not match.';
    } else {
        $con->begin_transaction();
        try {
            // Lock and re-check the token so two simultaneous submissions cannot both use it.
            $lock = $con->prepare('SELECT token_id, userid FROM password_reset_tokens WHERE token_hash = ? AND used_at IS NULL AND expires_at > NOW() LIMIT 1 FOR UPDATE');
            $lock->bind_param('s', $tokenHash);
            $lock->execute();
            $lockedRow = $lock->get_result()->fetch_assoc();
            $lock->close();

            if (!$lockedRow) {
                throw new RuntimeException('Reset token is no longer valid.');
            }

            $passwordHash = password_hash($password1, PASSWORD_DEFAULT);
            $update = $con->prepare('UPDATE userinfo SET userpass = ? WHERE userid = ?');
            $update->bind_param('si', $passwordHash, $lockedRow['userid']);
            $update->execute();
            $update->close();

            // Invalidate every outstanding reset token for this account.
            $consume = $con->prepare('UPDATE password_reset_tokens SET used_at = NOW() WHERE userid = ? AND used_at IS NULL');
            $consume->bind_param('i', $lockedRow['userid']);
            $consume->execute();
            $consume->close();

            $con->commit();
            $success = true;
        } catch (Throwable $e) {
            $con->rollback();
            error_log('GWST password reset failed: ' . $e->getMessage());
            $error = 'This password reset link could not be used. Please request a new one.';
        }
    }
}
?>
<main class="password-reset-shell">
    <section class="password-reset-card" aria-labelledby="reset-title">
        <div class="password-reset-brand">
            <span class="password-reset-mark">GWST</span>
            <span class="password-reset-kicker">Password Recovery</span>
        </div>

        <?php if ($success): ?>
            <div class="password-reset-status" aria-live="polite">
                <div class="password-reset-status-icon">✓</div>
                <h1 id="reset-title">Password updated</h1>
                <p>Your password has been changed successfully.</p>
                <a class="password-reset-primary-link" href="index.php">Login with your new password</a>
            </div>
        <?php elseif (!$resetRow): ?>
            <div class="password-reset-status password-reset-status-error">
                <div class="password-reset-status-icon">!</div>
                <h1 id="reset-title">Reset link unavailable</h1>
                <p><?= h($error !== '' ? $error : 'This password reset link is invalid or has expired.') ?></p>
                <a class="password-reset-primary-link" href="forgot-password.php">Request a new reset link</a>
            </div>
        <?php else: ?>
            <h1 id="reset-title">Choose a new password</h1>
            <p class="password-reset-intro">Enter your new password below. It must be at least 8 characters long.</p>
            <?php if ($error !== ''): ?><div class="password-reset-error" role="alert"><?= h($error) ?></div><?php endif; ?>
            <form method="post" action="reset-password.php" class="password-reset-form">
                <?= csrf_input() ?>
                <input type="hidden" name="token" value="<?= h($token) ?>">
                <label for="password1">New password</label>
                <input id="password1" name="password1" type="password" minlength="8" autocomplete="new-password" required autofocus>
                <label for="password2">Confirm new password</label>
                <input id="password2" name="password2" type="password" minlength="8" autocomplete="new-password" required>
                <button type="submit">Reset password</button>
            </form>
        <?php endif; ?>

        <?php if (!$success): ?><a class="password-reset-back" href="index.php">← Return to login</a><?php endif; ?>
    </section>
</main>
</body>
</html>
