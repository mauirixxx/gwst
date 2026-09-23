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
        // Count only titles whose actual points meet their configured maximum.
        // Do not trust the cached percentage: values such as 9,999 / 10,000
        // must not count as completed merely because a percentage rounded up.
        $ggr = $con->prepare(
            "SELECT COUNT(*)
             FROM gwstats gs
             WHERE gs.charid IN (0, ?)
               AND gs.gwamm = 0
               AND gs.accid = ?
               AND gs.userid = ?
               AND gs.titlepoints >= (
                   SELECT MAX(st.stpoints)
                   FROM gwsubtitles st
                   WHERE st.titlenameid = gs.titlenameid
               )"
        );
        $ggr->bind_param("iii", $_SESSION['prefcharid'], $_SESSION['prefaccid'], $_SESSION['userid']);
        $ggr->execute();
        $ggr->bind_result($gwamm);
        $ggr->fetch();
        $ggr->close();

        // $gcr = Get Current Rank
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
        $gpc = $con->prepare("SELECT MAX(stpoints) FROM gwsubtitles WHERE titlenameid = ?");
        $gpc->bind_param("i", $gwammid);
        $gpc->execute();
        $gpc->bind_result($pmr); //$pmr = Percentage Max Rank
        $gpc->fetch();
        $gpc->close();
        $progress = ($pmr > 0 && $gwamm >= $pmr) ? 100 : (($pmr > 0) ? (int)floor(($gwamm / $pmr) * 100) : 0);

        // $gcg = Get Character GWAMM (to see if we're tracking the GWAMM title or not)
        $gcg = $con->prepare("SELECT COUNT(*) FROM gwstats WHERE titlenameid = ? AND charid = ? AND accid = ? AND userid = ?");
        $gcg->bind_param("iiii", $gwammid, $_SESSION['prefcharid'], $_SESSION['prefaccid'], $_SESSION['userid']);
        $gcg->execute();
        $gcg->bind_result($cgs);
        $gcg->fetch();
        $gcg->close();
        if ($cgs > 0) {
            $ugt = $con->prepare("UPDATE gwstats SET stnameid = ?, titlepoints = ?, currentstrankname = ?, currentstrank = ?, percent = ? WHERE titlenameid = ? AND charid = ? AND accid = ? AND userid = ?");
            $ugt->bind_param("iisiiiiii", $stnameid, $gwamm, $stname, $strank, $progress, $gwammid, $_SESSION['prefcharid'], $_SESSION['prefaccid'], $_SESSION['userid']);
            $ugt->execute();
            $ugt->close();
        } else {
            $igt = $con->prepare("INSERT INTO gwstats (titlenameid, stnameid, titlepoints, currentstrankname, currentstrank, percent, gwamm, charid, accid, userid) VALUES (?, ?, ?, ?, ?, ?, 1, ?, ?, ?)");
            $igt->bind_param("iiisiiiii", $gwammid, $stnameid, $gwamm, $stname, $strank, $progress, $_SESSION['prefcharid'], $_SESSION['prefaccid'], $_SESSION['userid']);
            $igt->execute();
            $igt->close();
        }
    }
}
?>