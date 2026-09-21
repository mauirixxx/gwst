<?php
if (isset($_SESSION['userid']) && isset($_SESSION['admin']) && $_SESSION['admin'] == 1) {
    $stmtstins = $con->prepare("INSERT INTO gwsubtitles (titlenameid, stname, stpoints, strank) VALUES (?, ?, ?, ?)");
    $stmtstins->bind_param("isii", $_POST['titlenameid'], $_POST['titlerankname'], $_POST['titlepoints'], $_POST['titlerank']);
    $stmtstins->execute();
    $stmtstins->close();
    $_SESSION['title'] = "repeat";
    $_SESSION['tid'] = $_POST['titlenameid'];
    $_SESSION['tr'] = $_POST['titlerank'];
    $stmtname = $con->prepare("SELECT titlename FROM gwtitles WHERE titlenameid = ?");
    $stmtname->bind_param("i", $_POST['titlenameid']);
    $stmtname->execute();
    $stmtname->bind_result($title_name);
    $stmtname->fetch();
    $stmtname->close();
    echo 'Added rank <b>' . h($_POST['titlerankname']) . '</b> to <b>' . h($title_name) . '</b>, redirecting!';
    header ("Refresh:1; url=titlemanager.php");
}
?>