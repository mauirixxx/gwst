<?php
// Shared preferred-character update logic.
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

if (isset($_SESSION['userid']) && isset($_POST['prefcharid'])) {
    $postedPref = (string) $_POST['prefcharid'];

    if ($postedPref === 'nopref' || $postedPref === '0') {
        $ncp = $con->prepare("UPDATE userinfo SET prefcharid = 0, prefcharname = 'No default selected' WHERE userid = ?");
        $ncp->bind_param('i', $_SESSION['userid']);
        $ncp->execute();
        $ncp->close();
        $_SESSION['prefcharid'] = '0';
        $_SESSION['prefcharname'] = 'No default selected';
        $_SESSION['charprofid'] = '0';
        $preference_message = 'Preferred character cleared.';
    } else {
        $prefcharid = filter_var($postedPref, FILTER_VALIDATE_INT);
        if ($prefcharid === false || $prefcharid < 1 || empty($_SESSION['prefaccid'])) {
            $preference_message = 'Invalid character preference.';
        } else {
            $scp = $con->prepare('SELECT charid, charname, profid FROM gwchars WHERE charid = ? AND accid = ? AND userid = ?');
            $scp->bind_param('iii', $prefcharid, $_SESSION['prefaccid'], $_SESSION['userid']);
            $scp->execute();
            $result = $scp->get_result();
            if ($row = $result->fetch_assoc()) {
                $uap = $con->prepare('UPDATE userinfo SET prefcharid = ?, prefcharname = ? WHERE userid = ?');
                $uap->bind_param('isi', $prefcharid, $row['charname'], $_SESSION['userid']);
                $uap->execute();
                $uap->close();
                $_SESSION['prefcharid'] = $row['charid'];
                $_SESSION['prefcharname'] = $row['charname'];
                $_SESSION['charprofid'] = $row['profid'];
                $preference_message = 'Preferred character switched to ' . $row['charname'] . '.';
            } else {
                $preference_message = 'Character preference not found.';
            }
            $scp->close();
        }
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