<?php
if (isset($_SESSION['userid'])){
    // Keep the four composite Legendary titles in sync before counting maxed titles.
    include ('update-legendary-titles.php');

    // $ggid = Get Gwamm ID
    $ggid = $con->prepare("SELECT titlenameid FROM gwtitles WHERE gwamm = 1");
    $ggid->execute();
    $ggid->bind_result($gwammid);
    $ggid->fetch();
    $ggid->close();
    if ($gwammid == NULL) {
        echo 'No title has been set for GWAMM status - please do so --><a href="titlemanager.php" class="navlink">NOW</a><--<br />';
		include_once ('footer.php');
		exit();
    } else {
        // $ggr Get Gwamm Rank
        $ggr = $con->prepare("SELECT COUNT(*) FROM gwstats WHERE charid IN (0, ?) AND gwamm = 0 AND percent >= 100 AND accid = ? AND userid = ?");
        $ggr->bind_param("iii", $_SESSION['prefcharid'], $_SESSION['prefaccid'], $_SESSION['userid']);
        $ggr->execute();
        $ggr->bind_result($gwamm);
        $ggr->fetch();
        $ggr->close();
        // $gcr = Get Current Rank
        // Reset rank values before fetching. If the player has GWAMM progress
        // but has not earned the first rank yet, the query returns no row and
        // must not reuse values left over from the previously updated title.
        $stnameid = null;
        $stname = null;
        $strank = 0;
        $gcr = $con->prepare("SELECT stnameid, stname, strank FROM gwsubtitles WHERE titlenameid = ? AND stpoints <= ? ORDER BY stpoints DESC LIMIT 1");
        $gcr->bind_param("ii", $gwammid, $gwamm);
        $gcr->execute();
        $gcr->bind_result($stnameid, $stname, $strank);
        $gcr->fetch();
        $gcr->close();
    	// $gpc = Get Percentage Completed
        $gpc = $con->prepare("SELECT stpoints FROM gwsubtitles WHERE titlenameid = ? ORDER BY stnameid DESC LIMIT 1");
        $gpc->bind_param("i", $gwammid);
        $gpc->execute();
        $gpc->bind_result($pmr); //$pmr = Percentage Max Rank
        $gpc->fetch();
        $gpc->close();
    	$progress = ceil(($gwamm / $pmr) * 100);
        // $gcg = Get Character GWAMM (to see if we're tracking the GWAMM title or not)
        $gcg = $con->prepare("SELECT COUNT(*) FROM gwstats WHERE titlenameid = ? AND charid = ? AND accid = ? AND userid = ?");
        $gcg->bind_param("iiii", $gwammid, $_SESSION['prefcharid'], $_SESSION['prefaccid'], $_SESSION['userid']);
        $gcg->execute();
        $gcg->bind_result($cgs); // $cgs = Character GWAMM Status
        $gcg->fetch();
        $gcg->close();
        if ($cgs > 0) {
            // $ugt = Update GWAMM Title
            $ugt = $con->prepare("UPDATE gwstats SET stnameid = ?, titlepoints = ?, currentstrankname = ?, currentstrank = ?, percent = ? WHERE titlenameid = ? AND charid = ? AND accid = ? AND userid = ?");
            $ugt->bind_param("iisiiiiii", $stnameid, $gwamm, $stname, $strank, $progress, $gwammid, $_SESSION['prefcharid'], $_SESSION['prefaccid'], $_SESSION['userid']);
            $ugt->execute();
            $ugt->close();
        } else {
            // $igt = Insert GWAMM Title
            $igt = $con->prepare("INSERT INTO gwstats (titlenameid, stnameid, titlepoints, currentstrankname, currentstrank, percent, gwamm, charid, accid, userid) VALUES (?, ?, ?, ?, ?, ?, 1, ?, ?, ?)");
            $igt->bind_param("iiisiiiii", $gwammid, $stnameid, $gwamm, $stname, $strank, $progress, $_SESSION['prefcharid'], $_SESSION['prefaccid'], $_SESSION['userid']);
            $igt->execute();
            $igt->close();
        }
    }
}
?>