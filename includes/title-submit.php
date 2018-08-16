<?php
if (isset($_SESSION['userid'])) {
	$stmtins = $con->prepare("INSERT INTO gwtitles (titlename, titletype, titlemaxrank) VALUES (?, ?, ?)");
	$stmtins->bind_param("sii", $_POST['titlename'], $_POST['titletype'], $_POST['titlemaxrank']);
	$stmtins->execute();
	$stmtins->close();
	echo 'New title added!<br /><br />';
	$stmtview = $con->prepare("SELECT * FROM gwtitles ORDER BY titlenameid DESC LIMIT 1");
	$stmtview->execute();
	$result = $stmtview->get_result();
	while ($row = $result->fetch_assoc()) {
		$tid = $row['titlenameid'];
		$tname = $row['titlename'];
		$ttype = $row['titletype'];
	    $tmr = $row['titlemaxrank'];
		echo '<table border="1"><tr><th>titleid</th><th>titlename</th><th>titletype</th><th>titlemaxrank</th></tr>';
		echo '<tr><td>' . $tid . '</td><td>' . $tname . '</td><td>' . $ttype . '</td><td>' . $tmr . '</td></tr></table><br />';
	}
	$stmtview->close();
	echo 'Return to <a href="titlemanager.php" class="navlink">title manager</a>';
}
?>