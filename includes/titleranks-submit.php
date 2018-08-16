<?php
if (isset($_SESSION['userid'])) {
    $stmtstins = $con->prepare("INSERT INTO gwsubtitles (titlenameid, stname, stpoints, strank) VALUES (?, ?, ?, ?)");
    $stmtstins->bind_param("isii", $_POST['titlenameid'], $_POST['titlerankname'], $_POST['titlepoints'], $_POST['titlerank']);
    $stmtstins->execute();
    $stmtstins->close();
    $_SESSION['title'] = "repeat";
    $_SESSION['tid'] = $_POST['titlenameid'];
    $_SESSION['tr'] = $_POST['titlerank'];
    echo 'Title rank added, redirecting!';
    header ("Refresh:1; url=titlemanager.php");
}
?>