<?php
if (isset($_SESSION['userid'])){
	echo '<table border="1"><caption>Titles progress for <b>' . $_SESSION['prefcharname'] . '</b></caption>';
    echo '<tr><th>Title</th><th>Title Rank</th><th>Title Points</th><th>Current Rank</th><th>Points Remaining</th><th>Max Title %</th><th>Next Rank</th></tr>';
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
        // $gt = Get Title
        $gt = $con->prepare("SELECT titlename FROM gwtitles WHERE titlenameid = ?");
        $gt->bind_param("i", $row['titlenameid']);
        $gt->execute();
        $gt->bind_result($titlename);
        $gt->fetch();
        $gt->close();
        $pr = number_format(($stpoints - $row['titlepoints']));
        if ($pr <= 0) {
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
        echo '<tr><td style="width:175px;">' . $titlename . '</td><td style="width:210px;">' . $row['currentstrankname'] . '</td><td style="width:100px;">' . number_format($row['titlepoints']) . '</td><td style="width:70px;">' . $row['currentstrank'] . '</td>';
        echo '<td style="width:100px;">' . $pr . '</td><td><div class="percentbar" style="width:100px;"><div style="width:' . $ohp . 'px;"></div></div>';
		echo $ohp;
		echo '% completed</td><td>' . $stname . '</td></tr>';
    }
	$gccres->close();
	echo '</table><br />';
}
?>