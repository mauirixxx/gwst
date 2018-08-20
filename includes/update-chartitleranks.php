<?php
if (isset($_SESSION['userid'])) {
    // check to see if we're going to INSERT or UPDATE a row
    // $cfr = Check For Results
    $cfr = $con->prepare("SELECT COUNT(*) FROM gwstats WHERE charid = ? AND titlenameid = ? AND accid = ? AND userid = ?");
    $cfr->bind_param("iiii", $_SESSION['prefcharid'], $_POST['titlenameid'], $_SESSION['prefaccid'], $_SESSION['userid']);
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
	// $gpc = Get Percentage Completed
    $gpc = $con->prepare("SELECT stpoints FROM gwsubtitles WHERE titlenameid = ? ORDER BY stnameid DESC LIMIT 1");
    $gpc->bind_param("i", $_POST['titlenameid']);
    $gpc->execute();
    $gpc->bind_result($pmr); //$pmr = Percentage Max Rank
    $gpc->fetch();
    $gpc->close();
	$progress = ceil(($_POST['titlepoints'] / $pmr) * 100);
    if ($r1 > 0) {
        // $urs = Update Rank Stats
        $urs = $con->prepare("UPDATE gwstats SET stnameid = ?, titlepoints = ?, currentstrankname = ?, currentstrank = ?, percent = ? WHERE charid = ? AND titlenameid = ? AND accid = ? AND userid = ?");
        $urs->bind_param("iisiiiiii", $stnameid, $_POST['titlepoints'], $stname, $strank, $progress, $_SESSION['prefcharid'], $_POST['titlenameid'], $_SESSION['prefaccid'], $_SESSION['userid']);
        $urs->execute();
        $urs->close();
        echo 'Title has been updated!<br /><br />';
    } else {
        // $irs = Insert Rank Stats
        $irs = $con->prepare("INSERT INTO gwstats (titlenameid, stnameid, titlepoints, currentstrankname, currentstrank, percent, charid, accid, userid) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)");
        $irs->bind_param("iiisiiiii", $_POST['titlenameid'], $stnameid, $_POST['titlepoints'], $stname, $strank, $progress, $_SESSION['prefcharid'], $_SESSION['prefaccid'], $_SESSION['userid']);
        $irs->execute();
        $irs->close();
        echo 'Title entered!<br /></br />';
    }
}
?>