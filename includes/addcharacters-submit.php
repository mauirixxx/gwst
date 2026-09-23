<?php
if (isset($_SESSION['userid'])) {
    $prefaccid = (int)($_SESSION['prefaccid'] ?? 0);
    $charname = trim((string)($_POST['newcharname'] ?? ''));
    $birthdate = trim((string)($_POST['bdate'] ?? ''));
    $profid = filter_var($_POST['profid'] ?? null, FILTER_VALIDATE_INT);

    if ($charname === '' || mb_strlen($charname) > 19) {
        http_response_code(400);
        echo 'Character name must be between 1 and 19 characters.<br /><br />';
        return;
    }

    if ($profid === false || $profid < 1) {
        http_response_code(400);
        echo 'Invalid profession selected.<br /><br />';
        return;
    }

    if ($birthdate !== '') {
        $parsed_date = DateTimeImmutable::createFromFormat('!Y-m-d', $birthdate);
        $date_errors = DateTimeImmutable::getLastErrors();
        if (!$parsed_date || ($date_errors !== false && ($date_errors['warning_count'] > 0 || $date_errors['error_count'] > 0)) || $parsed_date->format('Y-m-d') !== $birthdate) {
            http_response_code(400);
            echo 'Invalid character birthdate.<br /><br />';
            return;
        }
    }

    // A character must belong to a real Guild Wars account owned by this user.
    $ownacc = $con->prepare("SELECT accid FROM gwaccounts WHERE accid = ? AND userid = ? LIMIT 1");
    $ownacc->bind_param("ii", $prefaccid, $_SESSION['userid']);
    $ownacc->execute();
    $owned_account = $ownacc->get_result()->fetch_assoc();
    $ownacc->close();

    if (!$owned_account) {
        http_response_code(400);
        echo 'Please add and select a Guild Wars account before adding a character.<br /><br />';
        return;
    }

    $pc = $con->prepare("SELECT profcolor FROM gwprofessions WHERE profid = ?");
    $pc->bind_param("i", $profid);
    $pc->execute();
    $prof = $pc->get_result()->fetch_assoc();
    $pc->close();

    if (!$prof) {
        http_response_code(400);
        echo 'Invalid profession selected.<br /><br />';
        return;
    }

    $profcolor = $prof['profcolor'];
    $ac = $con->prepare("INSERT INTO gwchars (accid, userid, charname, birthdate, profid, profcolor) VALUES (?, ?, ?, NULLIF(?, ''), ?, ?)");
    $ac->bind_param("iissis", $prefaccid, $_SESSION['userid'], $charname, $birthdate, $profid, $profcolor);
    if (!$ac->execute()) {
        $ac->close();
        http_response_code(500);
        echo 'Unable to add the character right now. Please try again.<br /><br />';
        return;
    }
    $ac->close();
    echo h($charname) . ' added to your account!<br /><br />';
}
?>