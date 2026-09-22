<?php
if (isset($_SESSION['userid'])){
	echo '<div class="stats-card home-title-progress"><div class="stats-table-wrap"><table class="stats-table"><caption>Titles progress for <b>' . h($_SESSION['prefcharname']) . '</b></caption>';
    echo '<thead><tr><th class="title-name">Title</th><th class="title-rank">Title Rank</th><th class="title-points">Title Points</th><th class="current-rank">Current Rank</th><th class="points-remaining">Points Remaining</th><th class="title-progress">Progress</th><th class="next-rank">Next Rank</th></tr></thead><tbody>';
	if ($_SESSION['prefcharid'] == "0") {
		// $gcc = Get Current Character stats
		$gcc = $con->prepare("SELECT * FROM gwstats WHERE charid = 0 AND accid = ? AND userid = ? ORDER BY currentstrank DESC, percent DESC");
		$gcc->bind_param("ii", $_SESSION['prefaccid'], $_SESSION['userid']);
	} else {
		// $gcc = Get Current Character stats
		$gcc = $con->prepare("SELECT * FROM gwstats WHERE charid IN (0, ?) AND accid = ? AND userid = ? ORDER BY currentstrank DESC, percent DESC");
		$gcc->bind_param("iii", $_SESSION['prefcharid'], $_SESSION['prefaccid'], $_SESSION['userid']);
	}
	$gcc->execute();
	$gccres = $gcc->get_result();
	while ($row = $gccres->fetch_assoc()) {
        // $gnr = Get Next Rank
        $gnr = $con->prepare("SELECT stpoints, stname FROM gwsubtitles WHERE titlenameid = ? AND stpoints >= ? ORDER BY stpoints ASC LIMIT 1");
        $gnr->bind_param("ii", $row['titlenameid'], $row['titlepoints']);
        $gnr->execute();
        $gnr->bind_result($stpoints, $stname);
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
        echo '<tr><td class="title-name">' . h($titlename) . '</td><td class="title-rank">' . h($row['currentstrankname']) . '</td><td class="title-points">' . number_format($row['titlepoints']) . '</td><td class="current-rank">' . h($row['currentstrank']) . '</td>';
        echo '<td class="points-remaining">' . h($pr) . '</td><td class="title-progress"><div class="progress-meter" role="progressbar" aria-valuenow="' . (int)$ohp . '" aria-valuemin="0" aria-valuemax="100"><div class="progress-fill" style="width:' . (int)$ohp . '%;"></div><span>' . (int)$ohp . '%</span></div></td><td class="next-rank">' . h($stname) . '</td></tr>';
    }
	$gccres->close();
	echo '</tbody></table></div></div>';
}
?>