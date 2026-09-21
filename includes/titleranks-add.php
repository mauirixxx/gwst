<?php
if (isset($_SESSION['userid']) && isset($_SESSION['admin']) && $_SESSION['admin'] == 1) {
	unset($_SESSION['title']);
	if (isset($_SESSION['tid'])) {
		$_POST['tid'] = $_SESSION['tid'];
	}
	if (isset($_SESSION['tr'])) {
		$tr = $_SESSION['tr'] + 1;
	} else {
		$trank = $con->prepare("SELECT MAX(strank) FROM gwsubtitles WHERE titlenameid = ?");
		$trank->bind_param("i", $_POST['tid']);
		$trank->execute();
		$trank->store_result();
		$trank->bind_result($gwstmr);
		while ($trank->fetch()) {
			if (is_null($gwstmr)) {
				$tr = 1;
			} else {
				$tr = $gwstmr + 1;
			}
		}
	}
	$stmtname = $con->prepare("SELECT titlename, titlemaxrank FROM gwtitles WHERE titlenameid = ?");
	$stmtname->bind_param("i", $_POST['tid']);
	$stmtname->execute();
	$stmtname->store_result();
	$stmtname->bind_result($gwtn, $gwtmr);
	while ($stmtname->fetch()) {
		echo 'Adding rank to title <b>' . h($gwtn) . '</b><br />The maximum rank achievable in game is ' . $gwtmr . '<br />';
		if ($tr > $gwtmr) {
			echo '<br />No more ranks can be added!<br /><br />';
		} else {
			echo '<form action="titlemanager.php" method="post"><table border="1"><tr><th>Title Rank Name</th><th>Title Points</th><th>Rank Level</th></tr>';
			echo '<tr><td><input type="text" name="titlerankname" required autofocus></td><td><input type="number" name="titlepoints" required></td><td><input type="number" readonly name="titlerank" min="1" max="15" value="' . $tr . '"></tr>';
			echo '</table><br /><input type="hidden" name="title" value="titleranksubmit"><input type="hidden" name="titlenameid" value="' . $_POST['tid'] . '"><input type="submit" value="Add title rank ..."></form><br />';
		}
	}
	$stmtname->free_result();
	$stmtname->close();
	echo 'Here are the currently associated title ranks, starting with rank 1:<br />';
	echo '<form action="titlemanager.php" method="post"><table border="1"><tr><th>stnameid</th><th>titlenameid</th><th>stname</th><th>stpoints</th><th>strank</th><th>Edit</th></tr>';
	$stmtview = $con->prepare("SELECT * FROM gwsubtitles WHERE titlenameid = ? ORDER BY strank ASC");
	$stmtview->bind_param("i", $_POST['tid']);
	$stmtview->execute();
	$result = $stmtview->get_result();
	while ($row = $result->fetch_assoc()) {
		$stnid = $row['stnameid'];
		$tnid = $row['titlenameid'];
		$stname = $row['stname'];
		$stpoints = $row['stpoints'];
		$strank = $row['strank'];
		echo '<tr><td>' . $stnid . '<td>' . $tnid . '</td><td>' . h($stname) . '</td><td>' . number_format($stpoints) . '</td><td>' . $strank . '</td><td><input type="checkbox" name="editstitle[]" value="' . $stnid . '"></td></tr>';
	}
	$stmtview->close();
	$_SESSION['tid'] = $_POST['tid'];
	echo '</table><br /><input type="hidden" name="title" value="modsubtitle"><input type="submit" value="Edit selected titles"></form><br />If anything looks off, please fix it!<br /><br />';
	echo 'Return to <a href="titlemanager.php" class="navlink">title manager</a>';
}
?>