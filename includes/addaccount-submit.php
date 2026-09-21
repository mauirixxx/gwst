<?php
if (isset($_SESSION['userid'])) {
    $accemail = trim($_POST['accemail']);

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
        $addacc->execute();
        $new_accid = $con->insert_id;
        $addacc->close();

        // The first account becomes the preferred account automatically.
        if ((int)$account_count === 0) {
            $setpref = $con->prepare("UPDATE userinfo SET prefaccid = ?, prefaccname = ? WHERE userid = ?");
            $setpref->bind_param("isi", $new_accid, $accemail, $_SESSION['userid']);
            $setpref->execute();
            $setpref->close();

            $_SESSION['prefaccid'] = $new_accid;
            $_SESSION['prefaccname'] = $accemail;
        }

        $con->commit();
    } catch (Throwable $e) {
        $con->rollback();
        throw $e;
    }

    echo 'New account added';
    if ((int)$account_count === 0) {
        echo ' and selected as your preferred account';
    }
    echo ', returning to editor.';
    header ("Refresh:1; url=addaccounts.php");
    exit();
}
?>