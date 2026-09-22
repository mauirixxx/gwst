<!DOCTYPE html>
<html>
<head>
<meta name="viewport" content="width=device-width, initial-scale=1">
<link rel="stylesheet" type="text/css" href="style.css?v=20260921-3">
<link rel="stylesheet" type="text/css" href="password-reset.css?v=20260921-1">
<title>Forgot password - GWST</title>
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
require_once __DIR__ . '/includes/mailer.php';

$con = mysqli_connect(DATABASE_HOST, DATABASE_USER, DATABASE_PASS, DATABASE_NAME);
if ($con->connect_errno) {
    die('Unable to connect to database.');
}

$message = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    csrf_require_valid_post();
    $identifier = trim((string)($_POST['identifier'] ?? ''));

    if ($identifier !== '') {
        $stmt = $con->prepare('SELECT userid, username, usermail FROM userinfo WHERE username = ? OR usermail = ? LIMIT 1');
        $stmt->bind_param('ss', $identifier, $identifier);
        $stmt->execute();
        $user = $stmt->get_result()->fetch_assoc();
        $stmt->close();

        if ($user && filter_var($user['usermail'], FILTER_VALIDATE_EMAIL)) {
            $delete = $con->prepare('DELETE FROM password_reset_tokens WHERE userid = ?');
            $delete->bind_param('i', $user['userid']);
            $delete->execute();
            $delete->close();

            $token = bin2hex(random_bytes(32));
            $tokenHash = hash('sha256', $token);

            // Let MariaDB calculate both created_at and expiry from the same clock.
            // This avoids PHP/MariaDB timezone differences extending token lifetime.
            $insert = $con->prepare('INSERT INTO password_reset_tokens (userid, token_hash, expires_at) VALUES (?, ?, DATE_ADD(NOW(), INTERVAL 1 HOUR))');
            $insert->bind_param('is', $user['userid'], $tokenHash);
            $insert->execute();
            $tokenId = $insert->insert_id;
            $insert->close();

            $scheme = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') ? 'https' : 'http';
            $host = $_SERVER['HTTP_HOST'] ?? '';
            $basePath = rtrim(str_replace('\\', '/', dirname($_SERVER['SCRIPT_NAME'] ?? '/')), '/');
            $resetUrl = $scheme . '://' . $host . $basePath . '/reset-password.php?token=' . rawurlencode($token);

            $subject = 'Reset your Guild Wars Stats Tracker password';
            $safeName = h($user['username']);
            $safeUrl = h($resetUrl);
            $html = '<h2>Reset your GWST password</h2>'
                . '<p>A password reset was requested for <strong>' . $safeName . '</strong>.</p>'
                . '<p><a href="' . $safeUrl . '">Reset your password</a></p>'
                . '<p>This link expires in 1 hour and can be used only once.</p>'
                . '<p>If you did not request this, you can ignore this e-mail.</p>';
            $text = "A password reset was requested for {$user['username']}.\n\nReset your password:\n{$resetUrl}\n\nThis link expires in 1 hour and can be used only once.\nIf you did not request this, you can ignore this e-mail.";

            // gwst_send_mail expects the plain-text body first, then optional HTML.
            $mailResult = gwst_send_mail($con, $user['usermail'], $subject, $text, $html);
            if (!$mailResult['success']) {
                $cleanup = $con->prepare('DELETE FROM password_reset_tokens WHERE token_id = ?');
                $cleanup->bind_param('i', $tokenId);
                $cleanup->execute();
                $cleanup->close();
                error_log('GWST password reset mail failed: ' . ($mailResult['message'] ?? 'Unknown mail error.'));
            }
        }
    }

    // Intentionally identical whether or not the account exists.
    $message = 'If that username or e-mail address matches a GWST account, a password reset link has been sent.';
}
?>
<main class="password-reset-shell">
    <section class="password-reset-card" aria-labelledby="recovery-title">
        <div class="password-reset-brand">
            <span class="password-reset-mark">GWST</span>
            <span class="password-reset-kicker">Password Recovery</span>
        </div>

        <?php if ($message !== ''): ?>
            <div class="password-reset-status" aria-live="polite">
                <div class="password-reset-status-icon">✓</div>
                <h1 id="recovery-title">Check your e-mail</h1>
                <p><?= h($message) ?></p>
            </div>
        <?php else: ?>
            <h1 id="recovery-title">Forgot your password?</h1>
            <p class="password-reset-intro">Enter your GWST username or signup e-mail address and we'll send you a one-time reset link.</p>
            <form method="post" action="forgot-password.php" class="password-reset-form">
                <?= csrf_input() ?>
                <label for="identifier">Username or e-mail</label>
                <input id="identifier" name="identifier" type="text" maxlength="255" autocomplete="username" required autofocus>
                <button type="submit">Send password reset link</button>
            </form>
        <?php endif; ?>

        <a class="password-reset-back" href="index.php">← Return to login</a>
    </section>
</main>
</body>
</html>
