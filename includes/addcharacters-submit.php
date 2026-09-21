<?php
if (isset($_SESSION['userid'])) {
    $prefaccid = (int)($_SESSION['prefaccid'] ?? 0);

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

    // $pc = ProfessionColor
    $pc = $con->prepare("SELECT profcolor FROM gwprofessions WHERE profid = ?");
    $pc->bind_param("i", $_POST['profid']);
    $pc->execute();
    $prof = $pc->get_result()->fetch_assoc();
    $pc->close();

    if (!$prof) {
        http_response_code(400);
        echo 'Invalid profession selected.<br /><br />';
        return;
    }

    $profcolor = $prof['profcolor'];
    // $ac = AddCharacter
    $ac = $con->prepare("INSERT INTO gwchars (accid, userid, charname, birthdate, profid, profcolor) VALUES (?, ?, ?, ?, ?, ?)");
    $ac->bind_param("iissis", $prefaccid, $_SESSION['userid'], $_POST['newcharname'], $_POST['bdate'], $_POST['profid'], $profcolor);
    $ac->execute();
    $ac->close();
    echo h($_POST['newcharname']) . ' added to your account!<br /><br />';
}
?>