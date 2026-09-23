<?php
const GWST_AUTH_WINDOW_SECONDS = 900;
const GWST_LOGIN_USER_LIMIT = 5;
const GWST_LOGIN_IP_LIMIT = 20;
const GWST_REGISTER_IP_LIMIT = 5;
const GWST_AUTH_BLOCK_SECONDS = 900;

function gwst_client_ip(): string
{
    $ip = $_SERVER['REMOTE_ADDR'] ?? '';
    return filter_var($ip, FILTER_VALIDATE_IP) ? $ip : 'unknown';
}

function gwst_throttle_key(string $scope, string $value): string
{
    return hash('sha256', $scope . ':' . strtolower($value));
}

function gwst_throttle_is_blocked(mysqli $con, string $action, string $key): bool
{
    $stmt = $con->prepare(
        'SELECT blocked_until IS NOT NULL AND blocked_until > NOW() AS is_blocked
         FROM auth_throttle
         WHERE throttle_key = ? AND action_type = ? LIMIT 1'
    );
    $stmt->bind_param('ss', $key, $action);
    $stmt->execute();
    $row = $stmt->get_result()->fetch_assoc();
    $stmt->close();

    return $row && (bool)$row['is_blocked'];
}

function gwst_throttle_record_failure(mysqli $con, string $action, string $key, int $limit): void
{
    $con->begin_transaction();
    try {
        $stmt = $con->prepare(
            'SELECT failure_count, window_started_at
             FROM auth_throttle
             WHERE throttle_key = ? AND action_type = ?
             FOR UPDATE'
        );
        $stmt->bind_param('ss', $key, $action);
        $stmt->execute();
        $row = $stmt->get_result()->fetch_assoc();
        $stmt->close();

        $windowExpired = !$row;
        if ($row) {
            $stmt = $con->prepare(
                'SELECT CAST(? AS DATETIME) < DATE_SUB(NOW(), INTERVAL ? SECOND) AS expired'
            );
            $windowStartedAt = (string)$row['window_started_at'];
            $window = GWST_AUTH_WINDOW_SECONDS;
            $stmt->bind_param('si', $windowStartedAt, $window);
            $stmt->execute();
            $windowExpired = (bool)$stmt->get_result()->fetch_assoc()['expired'];
            $stmt->close();
        }

        if ($windowExpired) {
            $stmt = $con->prepare(
                'INSERT INTO auth_throttle
                    (throttle_key, action_type, failure_count, window_started_at, blocked_until, updated_at)
                 VALUES (?, ?, 1, NOW(), NULL, NOW())
                 ON DUPLICATE KEY UPDATE
                    failure_count = 1,
                    window_started_at = NOW(),
                    blocked_until = NULL,
                    updated_at = NOW()'
            );
            $stmt->bind_param('ss', $key, $action);
        } else {
            $count = (int)$row['failure_count'] + 1;
            if ($count >= $limit) {
                $stmt = $con->prepare(
                    'UPDATE auth_throttle
                     SET failure_count = ?, blocked_until = DATE_ADD(NOW(), INTERVAL ? SECOND), updated_at = NOW()
                     WHERE throttle_key = ? AND action_type = ?'
                );
                $block = GWST_AUTH_BLOCK_SECONDS;
                $stmt->bind_param('iiss', $count, $block, $key, $action);
            } else {
                $stmt = $con->prepare(
                    'UPDATE auth_throttle
                     SET failure_count = ?, updated_at = NOW()
                     WHERE throttle_key = ? AND action_type = ?'
                );
                $stmt->bind_param('iss', $count, $key, $action);
            }
        }

        $stmt->execute();
        $stmt->close();
        $con->commit();
    } catch (Throwable $e) {
        $con->rollback();
        throw $e;
    }
}

function gwst_throttle_clear(mysqli $con, string $action, string $key): void
{
    $stmt = $con->prepare('DELETE FROM auth_throttle WHERE throttle_key = ? AND action_type = ?');
    $stmt->bind_param('ss', $key, $action);
    $stmt->execute();
    $stmt->close();
}

function gwst_throttle_cleanup(mysqli $con): void
{
    if (random_int(1, 100) !== 1) {
        return;
    }

    $con->query(
        'DELETE FROM auth_throttle
         WHERE updated_at < DATE_SUB(NOW(), INTERVAL 2 DAY)'
    );
}

function gwst_rate_limited_response(string $returnUrl = 'index.php'): void
{
    http_response_code(429);
    header('Retry-After: ' . GWST_AUTH_BLOCK_SECONDS);
    $safeReturnUrl = htmlspecialchars($returnUrl, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
    echo '<main class="auth-shell"><section class="auth-card">';
    echo '<div class="auth-brand"><div class="auth-brand-mark">GWTTT</div><div class="auth-brand-name">Guild Wars Titles &amp; Treasures Tracker</div></div>';
    echo '<h1>Too many requests</h1>';
    echo '<p class="auth-intro">Too many attempts were received. Please wait 15 minutes and try again.</p>';
    echo '<div class="auth-links"><a href="' . $safeReturnUrl . '">Return</a></div>';
    echo '</section></main>';
    exit();
}
?>
