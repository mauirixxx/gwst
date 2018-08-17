<?php
if (isset($_SESSION['userid'])) {
	if (!isset($_POST['autofill'])) {
		$_POST['autofill'] = "0";
	}
	$stmtins = $con->prepare("INSERT INTO gwtitles (titlename, titletype, titlemaxrank, autofilled) VALUES (?, ?, ?, ?)");
	$stmtins->bind_param("siii", $_POST['titlename'], $_POST['titletype'], $_POST['titlemaxrank'], $_POST['autofill']);
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
		$taf = $row['autofilled'];
		echo '<table border="1"><tr><th>titleid</th><th>titlename</th><th>titletype</th><th>titlemaxrank</th><th>autofilled</th></tr>';
		echo '<tr><td>' . $tid . '</td><td>' . $tname . '</td><td>' . $ttype . '</td><td>' . $tmr . '</td><td>' . $taf . '</tr></table><br />';
	}
	$stmtview->close();
	echo 'Return to <a href="titlemanager.php" class="navlink">title manager</a>';
}
?>