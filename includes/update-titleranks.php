<?php
if (isset($_SESSION['userid'])) {
    // check to see if we're going to INSERT or UPDATE a row
    // $cfr = Check For Results
    $cfr = $con->prepare("SELECT COUNT(*) FROM gwaccstats WHERE titlenameid = ? AND accid = ? AND userid = ?");
    $cfr->bind_param("iii", $_POST['titlenameid'], $_SESSION['prefaccid'], $_SESSION['userid']);
    $cfr->execute();
    $cfr->bind_result($r1);
    $cfr->fetch();
    $cfr->close();
    // $gcr = Get Current Rank
    $gcr = $con->prepare("SELECT stnameid, stname, strank FROM gwsubtitles WHERE titlenameid = ? AND stpoints <= ? ORDER BY stpoints DESC LIMIT 1");
    $gcr->bind_param("ii", $_POST['titlenameid'], $_POST['titlepoints']);
    $gcr->execute();
    $gcr->bind_result($stnameid, $stname, $strank);
    $gcr->fetch();
    $gcr->close();
    if ($r1 > 0) {
        // $urs = Update Rank Stats
        $urs = $con->prepare("UPDATE gwaccstats SET stnameid = ?, titlepoints = ?, currentstrankname = ?, currentstrank = ? WHERE titlenameid = ? AND accid = ? AND userid = ?");
        $urs->bind_param("iisiiii", $stnameid, $_POST['titlepoints'], $stname, $strank, $_POST['titlenameid'], $_SESSION['prefaccid'], $_SESSION['userid']);
        $urs->execute();
        $urs->close();
        echo 'Title has been updated!<br /><br />';
    } else {
        // $irs = Insert Rank Stats
        $irs = $con->prepare("INSERT INTO gwaccstats (titlenameid, stnameid, titlepoints, currentstrankname, currentstrank, accid, userid) VALUES (?, ?, ?, ?, ?, ?, ?)");
        $irs->bind_param("iiisiii", $_POST['titlenameid'], $stnameid, $_POST['titlepoints'], $stname, $strank, $_SESSION['prefaccid'], $_SESSION['userid']);
        $irs->execute();
        $irs->close();
        echo 'Title entered!<br /></br />';
    }
}
?>