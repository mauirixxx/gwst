<?php
if (isset($_SESSION['userid'])) {
    $addacc = $con->prepare("INSERT INTO gwaccounts (userid, accemail) VALUES (?, ?)");
    $addacc->bind_param("is", $_SESSION['userid'], $_POST['accemail']);
    $addacc->execute();
    $addacc->close();
    echo 'New account added, returning to editor.';
    header ("Refresh:1; url=addaccounts.php");
    exit();
}
?>