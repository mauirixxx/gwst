<?php
if (session_status() === PHP_SESSION_NONE) {
    ini_set('session.use_strict_mode', '1');
    ini_set('session.use_only_cookies', '1');
    ini_set('session.cookie_httponly', '1');
    ini_set('session.cookie_samesite', 'Lax');
    if (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') ini_set('session.cookie_secure', '1');
    session_start();
}
ob_start();
require_once __DIR__ . '/csrf.php';
require_once dirname(__DIR__) . '/connect.php';

$wantsJson = isset($_SERVER['HTTP_ACCEPT']) && str_contains($_SERVER['HTTP_ACCEPT'], 'application/json');
function tonic_respond(bool $ok, string $message, array $data = [], int $status = 200): never {
    global $wantsJson;
    if ($wantsJson) {
        if (ob_get_level()) ob_clean();
        http_response_code($status);
        header('Content-Type: application/json; charset=UTF-8');
        header('Cache-Control: no-store');
        echo json_encode(array_merge(['ok'=>$ok,'message'=>$message], $data), JSON_UNESCAPED_SLASHES);
        exit;
    }
    $_SESSION['preference_message'] = $message;
    header('Location: ../miniatures.php');
    exit;
}
if (empty($_SESSION['userid'])) tonic_respond(false, 'Your session has expired. Please sign in again.', [], 401);
if ($_SERVER['REQUEST_METHOD'] !== 'POST') tonic_respond(false, 'POST required.', [], 405);
if (!csrf_validate_token($_POST['csrf_token'] ?? null)) tonic_respond(false, 'Invalid or expired security token. Refresh the page and try again.', [], 403);

$con = mysqli_connect(DATABASE_HOST, DATABASE_USER, DATABASE_PASS, DATABASE_NAME);
if (!$con || $con->connect_errno) tonic_respond(false, 'Unable to connect to database.', [], 500);
$con->set_charset('utf8mb4');
$userid=(int)$_SESSION['userid']; $accid=(int)($_SESSION['prefaccid']??0); $tonicid=(int)($_POST['tonicid']??0);
$raw=trim((string)($_POST['quantity']??''));
if ($accid<=0 || $tonicid<=0) tonic_respond(false,'Select a Guild Wars account before updating tonics.',[],400);
if ($raw==='' || !ctype_digit($raw)) tonic_respond(false,'On-hand quantity must be a whole number of 0 or greater.',[],422);
$quantity=(int)$raw; if ($quantity>999999) tonic_respond(false,'On-hand quantity is too large.',[],422);

$stmt=$con->prepare('SELECT 1 FROM gwaccounts WHERE accid=? AND userid=? LIMIT 1'); $stmt->bind_param('ii',$accid,$userid); $stmt->execute(); $owns=(bool)$stmt->get_result()->fetch_row(); $stmt->close();
$stmt=$con->prepare('SELECT 1 FROM gwtonics WHERE tonicid=? LIMIT 1'); $stmt->bind_param('i',$tonicid); $stmt->execute(); $valid=(bool)$stmt->get_result()->fetch_row(); $stmt->close();
if (!$owns || !$valid) tonic_respond(false,'Unable to update that tonic.',[],403);
if ($quantity===0) {
    $stmt=$con->prepare('DELETE FROM gwtonic_inventory WHERE userid=? AND accid=? AND tonicid=?'); $stmt->bind_param('iii',$userid,$accid,$tonicid); $stmt->execute(); $stmt->close();
} else {
    $stmt=$con->prepare('INSERT INTO gwtonic_inventory (userid,accid,tonicid,quantity) VALUES (?,?,?,?) ON DUPLICATE KEY UPDATE quantity=VALUES(quantity)'); $stmt->bind_param('iiii',$userid,$accid,$tonicid,$quantity); $stmt->execute(); $stmt->close();
}
$stmt=$con->prepare('SELECT COALESCE(SUM(quantity),0) FROM gwtonic_inventory WHERE userid=? AND accid=?'); $stmt->bind_param('ii',$userid,$accid); $stmt->execute(); $stmt->bind_result($total); $stmt->fetch(); $stmt->close(); $con->close();
tonic_respond(true,'Tonic updated.',['tonicid'=>$tonicid,'quantity'=>$quantity,'tonic_total'=>(int)$total]);
