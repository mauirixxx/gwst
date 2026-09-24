<?php
$pagetitle = 'Update miniature';
include_once (__DIR__ . '/../header.php');

if (!isset($_SESSION['userid'])) {
    exit;
}

$userid = (int)$_SESSION['userid'];
$accid = (int)($_SESSION['prefaccid'] ?? 0);
$miniid = (int)($_POST['miniid'] ?? 0);
$action = (string)($_POST['action'] ?? '');

if ($accid <= 0 || $miniid <= 0) {
    $_SESSION['preference_message'] = 'Select a Guild Wars account before updating miniatures.';
    header('Location: ../miniatures.php');
    exit;
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
    $_SESSION['preference_message'] = 'Unable to update that miniature.';
    header('Location: ../miniatures.php');
    exit;
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
        $_SESSION['preference_message'] = 'On-hand quantity must be a whole number of 0 or greater.';
        header('Location: ../miniatures.php');
        exit;
    }
    $currentQuantity = (int)$rawQuantity;
    if ($currentQuantity > 999999) {
        $_SESSION['preference_message'] = 'On-hand quantity is too large.';
        header('Location: ../miniatures.php');
        exit;
    }
} else {
    $_SESSION['preference_message'] = 'Unknown miniature update.';
    header('Location: ../miniatures.php');
    exit;
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

header('Location: ../miniatures.php');
exit;
