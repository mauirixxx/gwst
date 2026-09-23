<?php
if (isset($_SESSION['userid'])) {
    $accemail = trim((string)($_POST['accemail'] ?? ''));

    if ($accemail === '' || mb_strlen($accemail) > 255) {
        http_response_code(400);
        echo 'Account e-mail or alias must be between 1 and 255 characters.<br /><br />';
        return;
    }

    // Determine whether this is the user's first Guild Wars account.
    $countacc = $con->prepare("SELECT COUNT(*) FROM gwaccounts WHERE userid = ?");
    $countacc->bind_param("i", $_SESSION['userid']);
    $countacc->execute();
    $countacc->bind_result($account_count);
    $countacc->fetch();
    $countacc->close();

    $con->begin_transaction();
    try {
        $addacc = $con->prepare("INSERT INTO gwaccounts (userid, accemail) VALUES (?, ?)");
        $addacc->bind_param("is", $_SESSION['userid'], $accemail);
        if (!$addacc->execute()) {
            throw new RuntimeException('Unable to add account.');
        }
        $new_accid = $con->insert_id;
        $addacc->close();

        // The first account becomes the preferred account automatically.
        if ((int)$account_count === 0) {
            $setpref = $con->prepare("UPDATE userinfo SET prefaccid = ?, prefaccname = ? WHERE userid = ?");
            $setpref->bind_param("isi", $new_accid, $accemail, $_SESSION['userid']);
            if (!$setpref->execute()) {
                throw new RuntimeException('Unable to set preferred account.');
            }
            $setpref->close();
        }

        $con->commit();
    } catch (Throwable $e) {
        $con->rollback();
        error_log('Account creation failed for user ' . (int)$_SESSION['userid'] . ': ' . $e->getMessage());
        http_response_code(500);
        echo 'Unable to add the account right now. Please try again.<br /><br />';
        return;
    }

    if ((int)$account_count === 0) {
        $_SESSION['prefaccid'] = $new_accid;
        $_SESSION['prefaccname'] = $accemail;
    }

    echo 'New account added';
    if ((int)$account_count === 0) {
        echo ' and selected as your preferred account';
    }
    echo ', returning to editor.';
    header("Refresh:1; url=addaccounts.php");
    exit();
}
?>