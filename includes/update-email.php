<?php
if (isset($_SESSION['userid'])) {
    include_once ('verifications.php');

    $new_email = trim($_POST['useremail'] ?? '');

    if (!filter_var($new_email, FILTER_VALIDATE_EMAIL)) {
        echo 'Please enter a valid e-mail address.<br />';
        return;
    }

    // A signup e-mail address identifies one GWST user. Allow the current
    // user to keep their existing address, but reject an address owned by
    // any other account.
    $checkmail = $con->prepare("SELECT userid FROM userinfo WHERE usermail = ? AND userid <> ? LIMIT 1");
    $checkmail->bind_param("si", $new_email, $_SESSION['userid']);
    $checkmail->execute();
    $duplicate = $checkmail->get_result()->fetch_assoc();
    $checkmail->close();

    if ($duplicate) {
        echo 'That e-mail address is already associated with another GWST account.<br />';
        return;
    }

    $updmail = $con->prepare("UPDATE userinfo SET usermail = ? WHERE userid = ?");
    $updmail->bind_param("si", $new_email, $_SESSION['userid']);

    try {
        $updmail->execute();
        $_SESSION['usermail'] = $new_email;
        echo 'E-mail address updated.<br />';
    } catch (mysqli_sql_exception $e) {
        // The database UNIQUE constraint is the final line of defense in
        // case another request claims the same address after our check.
        if ((int)$e->getCode() === 1062) {
            echo 'That e-mail address is already associated with another GWST account.<br />';
        } else {
            error_log('GWST e-mail update failed: ' . $e->getMessage());
            echo 'Unable to update e-mail address.<br />';
        }
    } finally {
        $updmail->close();
    }
}
?>