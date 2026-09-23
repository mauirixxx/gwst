<?php
if (isset($_SESSION['userid'])) {
    $oldpass = $_POST['oldpass'] ?? '';
    $newpass = $_POST['userpass1'] ?? '';
    $newpass_confirm = $_POST['userpass2'] ?? '';

    if (!is_string($oldpass) || !is_string($newpass) || !is_string($newpass_confirm)) {
        http_response_code(400);
        echo 'Invalid password update request.<br />';
        return;
    }

    $password_length = strlen($newpass);
    if ($password_length < 8 || $password_length > 255) {
        echo 'New password must be between 8 and 255 characters long.<br />';
        return;
    }

    if ($newpass !== $newpass_confirm) {
        echo 'New passwords do not match.<br />';
        return;
    }

    $verifypass = $con->prepare("SELECT userpass FROM userinfo WHERE userid = ? LIMIT 1");
    $verifypass->bind_param("i", $_SESSION['userid']);
    $verifypass->execute();
    $row = $verifypass->get_result()->fetch_assoc();
    $verifypass->close();

    if (!$row || !password_verify($oldpass, $row['userpass'])) {
        echo 'Old password doesn\'t match, password is NOT updated!<br />';
        return;
    }

    $hp = password_hash($newpass, PASSWORD_DEFAULT);
    if ($hp === false) {
        error_log('GWTTT password hashing failed for userid ' . (int)$_SESSION['userid']);
        echo 'Unable to update password.<br />';
        return;
    }

    $updpass = $con->prepare("UPDATE userinfo SET userpass = ? WHERE userid = ?");
    $updpass->bind_param("si", $hp, $_SESSION['userid']);
    $updpass->execute();
    $updpass->close();

    echo 'Password updated!<br />';
}
?>