<?php
if (isset($_SESSION['userid']) && isset($_SESSION['admin']) && $_SESSION['admin'] == 1) {
    if (isset($_POST['delsubtitle'])) {
        if ($delst = $con->prepare("DELETE FROM gwsubtitles WHERE titlenameid = ? AND stnameid = ?")) {
            $delst->bind_param("ii", $tnameid, $stnameid);
            for ($i = 0; $i < count($_POST['delsubtitle']); $i++) {
                $tnameid = $_POST['titlenameid'][$i];
                $stnameid = $_POST['delsubtitle'][$i];
                $delst->execute();
            }
            $delst->close();
        }
        echo 'Title rank(s) have been deleted, redirecting!';
        header ("Refresh:1; url=titlemanager.php");
    } else {
        // this section updates the title name
        if ($upd = $con->prepare("UPDATE gwsubtitles SET stname = ?, stpoints = ?, strank = ? WHERE titlenameid = ? AND stnameid = ?")) {
            $upd->bind_param("siiii", $stname, $stpoints, $strank, $titlenameid, $stnameid);
            for ($i = 0; $i < count($_POST['stname']); $i++) {
                $stname = $_POST['stname'][$i];
                $stpoints = $_POST['stpoints'][$i];
                $strank = $_POST['strank'][$i];
                $titlenameid = $_POST['titlenameid'][$i];
                $stnameid = $_POST['stnameid'][$i];
                $upd->execute();
            }
            $upd->close();
        }
        echo 'Title rank(s) updated, redirecting!';
        header ("Refresh:1; url=titlemanager.php");
    }
}
?>