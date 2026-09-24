<?php
if (session_status() === PHP_SESSION_NONE) {
    ini_set('session.use_strict_mode', '1');
    ini_set('session.use_only_cookies', '1');
    ini_set('session.cookie_httponly', '1');
    ini_set('session.cookie_samesite', 'Lax');
    if (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') {
        ini_set('session.cookie_secure', '1');
    }
    session_start();
}

require_once __DIR__ . '/csrf.php';
require_once dirname(__DIR__) . '/connect.php';

$wantsJson = isset($_SERVER['HTTP_ACCEPT']) && str_contains($_SERVER['HTTP_ACCEPT'], 'application/json');

function miniature_respond(bool $ok, string $message, array $data = [], int $status = 200): never
{
    global $wantsJson;
    if ($wantsJson) {
        http_response_code($status);
        header('Content-Type: application/json; charset=UTF-8');
        header('Cache-Control: no-store');
        echo json_encode(array_merge(['ok' => $ok, 'message' => $message], $data), JSON_UNESCAPED_SLASHES);
        exit;
    }
    $_SESSION['preference_message'] = $message;
    header('Location: ../miniatures.php');
    exit;
}

if (empty($_SESSION['userid'])) {
    miniature_respond(false, 'Your session has expired. Please sign in again.', [], 401);
}
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    if ($wantsJson) miniature_respond(false, 'POST required.', [], 405);
    header('Location: ../miniatures.php');
    exit;
}
csrf_require_valid_post();

$con = mysqli_connect(DATABASE_HOST, DATABASE_USER, DATABASE_PASS, DATABASE_NAME);
if (!$con || $con->connect_errno) {
    miniature_respond(false, 'Unable to connect to database.', [], 500);
}
$con->set_charset('utf8mb4');

$userid = (int)$_SESSION['userid'];
$accid = (int)($_SESSION['prefaccid'] ?? 0);
$miniid = (int)($_POST['miniid'] ?? 0);
$action = (string)($_POST['action'] ?? '');

if ($accid <= 0 || $miniid <= 0) {
    miniature_respond(false, 'Select a Guild Wars account before updating miniatures.', [], 400);
}

// Never trust the session account id or submitted miniature id without proving
// both belong to the authenticated user's current tracker context.
$accountCheck = $con->prepare('SELECT 1 FROM gwaccounts WHERE accid = ? AND userid = ? LIMIT 1');
$accountCheck->bind_param('ii', $accid, $userid);
$accountCheck->execute();
$ownsAccount = (bool)$accountCheck->get_result()->fetch_row();
$accountCheck->close();

$miniCheck = $con->prepare('SELECT 1 FROM gwminiatures WHERE miniid = ? LIMIT 1');
$miniCheck->bind_param('i', $miniid);
$miniCheck->execute();
$validMini = (bool)$miniCheck->get_result()->fetch_row();
$miniCheck->close();

if (!$ownsAccount || !$validMini) {
    miniature_respond(false, 'Unable to update that miniature.', [], 403);
}

$currentDedicated = 0;
$currentQuantity = 0;
$current = $con->prepare('SELECT dedicated, quantity FROM gwminiature_inventory WHERE userid = ? AND accid = ? AND miniid = ? LIMIT 1');
$current->bind_param('iii', $userid, $accid, $miniid);
$current->execute();
$currentRow = $current->get_result()->fetch_assoc();
$current->close();
if ($currentRow) {
    $currentDedicated = (int)$currentRow['dedicated'];
    $currentQuantity = (int)$currentRow['quantity'];
}

if ($action === 'toggle_dedicated') {
    $currentDedicated = $currentDedicated ? 0 : 1;
} elseif ($action === 'set_quantity') {
    $rawQuantity = trim((string)($_POST['quantity'] ?? ''));
    if ($rawQuantity === '' || !ctype_digit($rawQuantity)) {
        miniature_respond(false, 'On-hand quantity must be a whole number of 0 or greater.', [], 422);
    }
    $currentQuantity = (int)$rawQuantity;
    if ($currentQuantity > 999999) {
        miniature_respond(false, 'On-hand quantity is too large.', [], 422);
    }
} else {
    miniature_respond(false, 'Unknown miniature update.', [], 400);
}

if ($currentDedicated === 0 && $currentQuantity === 0) {
    $delete = $con->prepare('DELETE FROM gwminiature_inventory WHERE userid = ? AND accid = ? AND miniid = ?');
    $delete->bind_param('iii', $userid, $accid, $miniid);
    $delete->execute();
    $delete->close();
} else {
    $save = $con->prepare(
        'INSERT INTO gwminiature_inventory (userid, accid, miniid, dedicated, quantity) VALUES (?, ?, ?, ?, ?) '
        . 'ON DUPLICATE KEY UPDATE dedicated = VALUES(dedicated), quantity = VALUES(quantity)'
    );
    $save->bind_param('iiiii', $userid, $accid, $miniid, $currentDedicated, $currentQuantity);
    $save->execute();
    $save->close();
}

$summary = $con->prepare('SELECT COALESCE(SUM(dedicated),0), COALESCE(SUM(quantity),0) FROM gwminiature_inventory WHERE userid = ? AND accid = ?');
$summary->bind_param('ii', $userid, $accid);
$summary->execute();
$summary->bind_result($dedicatedCount, $quantityTotal);
$summary->fetch();
$summary->close();
$con->close();

miniature_respond(true, 'Miniature updated.', [
    'miniid' => $miniid,
    'dedicated' => $currentDedicated,
    'quantity' => $currentQuantity,
    'dedicated_count' => (int)$dedicatedCount,
    'quantity_total' => (int)$quantityTotal,
]);
