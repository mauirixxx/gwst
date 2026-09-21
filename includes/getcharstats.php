<?php
if (isset($_SESSION['userid'])) {
    echo '<table border="1"><caption>Character stats</caption>';
    echo '<tr><th class=\"title-name\">Title</th><th class=\"title-rank\">Title Rank</th><th class=\"title-points\">Title Points</th><th class=\"current-rank\">Current Rank</th><th class=\"points-remaining\">Points Remaining</th><th class=\"title-progress\">Max Title %</th><th class=\"next-rank\">Next Rank</th></tr>';
    // $gcs = Get Character Stats
    $gcs = $con->prepare("SELECT * FROM gwstats WHERE charid = ? AND accid = ? AND userid = ? ORDER BY percent DESC, currentstrank DESC, percent ASC");
    $gcs->bind_param("iii", $_SESSION['prefcharid'], $_SESSION['prefaccid'], $_SESSION['userid']);
    $gcs->execute();
    $result = $gcs->get_result();
    while ($row = $result->fetch_assoc()) {
        // $gnr = Get Next Rank
        $gnr = $con->prepare("SELECT stpoints, stname, strank FROM gwsubtitles WHERE titlenameid = ? AND stpoints >= ? ORDER BY stpoints ASC LIMIT 1");
        $gnr->bind_param("ii", $row['titlenameid'], $row['titlepoints']);
        $gnr->execute();
        $gnr->bind_result($stpoints, $stname, $strank);
        $gnr->fetch();
        $gnr->close();
        // $gmr = Get Maximum Rank available for selected title
        $gmr = $con->prepare("SELECT MAX(strank), MAX(stpoints) FROM gwsubtitles WHERE titlenameid = ?");
        $gmr->bind_param("i", $row['titlenameid']);
        $gmr->execute();
        $gmr->bind_result($mra, $mpa); // $mra = max rank available, $mpa = max points available
        $gmr->fetch();
        $gmr->close();
        // $gt = Get Title
        $gt = $con->prepare("SELECT titlename FROM gwtitles WHERE titlenameid = ?");
        $gt->bind_param("i", $row['titlenameid']);
        $gt->execute();
        $gt->bind_result($titlename);
        $gt->fetch();
        $gt->close();
        $pr = number_format(($mpa - $row['titlepoints']));
        if ($row['currentstrank'] === $mra) {
            $pr = "Highest rank achieved!";
            $stname = "Highest rank achieved!";
        }
        if ($row['currentstrankname'] === NULL) {
            $row['currentstrankname'] = "No title earned yet!";
            $row['currentstrank'] = "0";
        }
		if ($row['percent'] >= 100) {
			$ohp = 100;
		} else {
			$ohp = $row['percent'];
		}
        echo '<tr><td style="width:175px;">' . h($titlename) . '</td><td style="width:210px;">' . h($row['currentstrankname']) . '</td><td style="width:100px;">' . number_format($row['titlepoints']) . '</td><td style="width:70px;">' . $row['currentstrank'] . '</td>';
        echo '<td style="width:100px;">' . $pr . '</td><td><div class="percentbar" style="width:100px;"><div style="width:' . $ohp . 'px;"></div></div>';
		echo $ohp;
		echo '% completed</td><td>' . h($stname) . '</td></tr>';
    }
    $gcs->close();
    echo '</table><br />';
}
?>