<?php
if (isset($_SESSION['userid'])) {
    echo '<table border="1"><caption>Account wide stats</caption>';
    echo '<tr><th>Title</th><th>Title Rank</th><th>Title Points</th><th>Current Rank</th><th>Points Remaining</th><th>Max Title %</th><th>Next Rank</th></tr>';
    // $gas = GetAccountStats
    $gas = $con->prepare("SELECT * FROM gwaccstats WHERE userid = ? AND accid = ? ORDER BY currentstrank DESC, percent ASC");
    $gas->bind_param("ii", $_SESSION['userid'], $_SESSION['prefaccid']);
    $gas->execute();
    $result = $gas->get_result();
    while ($row = $result->fetch_assoc()) {
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
        if ($pr < 0) {
            $pr = "Highest rank achieved!";
            $stname = "Highest rank achieved!";
        }
        if ($row['currentstrankname'] === NULL) {
            $row['currentstrankname'] = "No title earned yet!";
            $row['currentstrank'] = "0";
        }
		if ($row['percent'] > 100) {
			$ohp = 100;
		} else {
			$ohp = $row['percent'];
		}
        echo '<tr><td style="width:150px;">' . $titlename . '</td><td style="width:200px;">' . $row['currentstrankname'] . '</td><td style="width:100px;">' . number_format($row['titlepoints']) . '</td><td style="width:70px;">' . $row['currentstrank'] . '</td>';
        echo '<td style="width:100px;">' . $pr . '</td><td><div class="percentbar" style="width:100px;"><div style="width:' . $ohp . 'px;"></div></div>';
		echo $ohp;
		echo '% completed</td><td>' . $stname . '</td></tr>';
    }
    $gas->close();
    echo '</table><br />';
}
?>