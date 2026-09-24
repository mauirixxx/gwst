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

if (empty($_SESSION['userid'])) {
    header('Location: ../index.php');
    exit;
}
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: ../miniatures.php');
    exit;
}
csrf_require_valid_post();

$con = mysqli_connect(DATABASE_HOST, DATABASE_USER, DATABASE_PASS, DATABASE_NAME);
if (!$con || $con->connect_errno) {
    http_response_code(500);
    exit('Unable to connect to database.');
}
$con->set_charset('utf8mb4');

$userid = (int)$_SESSION['userid'];
$accid = (int)($_SESSION['prefaccid'] ?? 0);
$miniid = (int)($_POST['miniid'] ?? 0);
$action = (string)($_POST['action'] ?? '');

function miniature_return(string $message): never
{
    $_SESSION['preference_message'] = $message;
    header('Location: ../miniatures.php');
    exit;
}

if ($accid <= 0 || $miniid <= 0) {
    miniature_return('Select a Guild Wars account before updating miniatures.');
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
    miniature_return('Unable to update that miniature.');
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
        miniature_return('On-hand quantity must be a whole number of 0 or greater.');
    }
    $currentQuantity = (int)$rawQuantity;
    if ($currentQuantity > 999999) {
        miniature_return('On-hand quantity is too large.');
    }
} else {
    miniature_return('Unknown miniature update.');
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

$con->close();
miniature_return('Miniature updated.');
