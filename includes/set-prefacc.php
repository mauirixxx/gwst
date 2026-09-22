<?php
// Shared preferred-account update logic.
// This file is included by preferences.php and can also receive the header form directly.
$standalonePreferenceRequest = (basename($_SERVER['SCRIPT_FILENAME'] ?? '') === basename(__FILE__));

if ($standalonePreferenceRequest) {
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
        header('Location: ../landing.php');
        exit;
    }

    csrf_require_valid_post();

    $con = mysqli_connect(DATABASE_HOST, DATABASE_USER, DATABASE_PASS, DATABASE_NAME);
    if (!$con || $con->connect_errno) {
        http_response_code(500);
        exit('Unable to connect to database.');
    }
    $con->set_charset('utf8mb4');
}

$preference_message = '';

if (isset($_SESSION['userid']) && isset($_POST['prefaccid'])) {
    $postedPref = (string) $_POST['prefaccid'];
    $accountChanged = false;

    if ($postedPref === 'nopref' || $postedPref === '0') {
        $nap = $con->prepare("UPDATE userinfo SET prefaccid = 0, prefaccname = 'No default selected' WHERE userid = ?");
        $nap->bind_param('i', $_SESSION['userid']);
        $nap->execute();
        $nap->close();
        $_SESSION['prefaccid'] = '0';
        $_SESSION['prefaccname'] = 'No default selected';
        $preference_message = 'Preferred Guild Wars account cleared.';
        $accountChanged = true;
    } else {
        $prefaccid = filter_var($postedPref, FILTER_VALIDATE_INT);
        if ($prefaccid === false || $prefaccid < 1) {
            $preference_message = 'Invalid account preference.';
        } else {
            $sap = $con->prepare('SELECT accid, accemail FROM gwaccounts WHERE accid = ? AND userid = ?');
            $sap->bind_param('ii', $prefaccid, $_SESSION['userid']);
            $sap->execute();
            $result = $sap->get_result();
            if ($row = $result->fetch_assoc()) {
                $uap = $con->prepare('UPDATE userinfo SET prefaccid = ?, prefaccname = ? WHERE userid = ?');
                $uap->bind_param('isi', $prefaccid, $row['accemail'], $_SESSION['userid']);
                $uap->execute();
                $uap->close();
                $_SESSION['prefaccid'] = $row['accid'];
                $_SESSION['prefaccname'] = $row['accemail'];
                $preference_message = 'Preferred Guild Wars account switched to ' . $row['accemail'] . '.';
                $accountChanged = true;
            } else {
                $preference_message = 'Account preference not found.';
            }
            $sap->close();
        }
    }

    if ($accountChanged) {
        $ncp = $con->prepare("UPDATE userinfo SET prefcharid = 0, prefcharname = 'No default selected' WHERE userid = ?");
        $ncp->bind_param('i', $_SESSION['userid']);
        $ncp->execute();
        $ncp->close();
        $_SESSION['prefcharid'] = '0';
        $_SESSION['prefcharname'] = 'No default selected';
        $_SESSION['charprofid'] = '0';
    }
}

if ($standalonePreferenceRequest) {
    $_SESSION['preference_message'] = $preference_message;
    $con->close();
    $returnTo = $_SERVER['HTTP_REFERER'] ?? '../landing.php';
    if (!is_string($returnTo) || $returnTo === '' || preg_match('/[\r\n]/', $returnTo)) {
        $returnTo = '../landing.php';
    }
    header('Location: ' . $returnTo);
    exit;
}
?>