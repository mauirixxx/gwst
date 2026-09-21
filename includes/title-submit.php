<?php
if (isset($_SESSION['userid']) && isset($_SESSION['admin']) && $_SESSION['admin'] == 1) {
	if (!isset($_POST['autofill'])) {
		$_POST['autofill'] = 0;
	}
	if (!isset($_POST['gwamm'])) {
			$_POST['gwamm'] = 0;
	} else {
		// $ggid = Get Gwamm ID
		$ggid = $con->prepare("SELECT titlenameid FROM gwtitles WHERE gwamm = 1");
		$ggid->execute();
		$ggid->bind_result($gwammid);
		$ggid->fetch();
		$ggid->close();
		// $rg = Remove GWAMM
		$rg = $con->prepare("UPDATE gwtitles SET gwamm = 0 WHERE titlenameid = ?");
		$rg->bind_param("i", $gwammid);
		$rg->execute();
		$rg->close();
	}
	$stmtins = $con->prepare("INSERT INTO gwtitles (titlename, titletype, titlemaxrank, autofilled, gwamm) VALUES (?, ?, ?, ?, ?)");
	$stmtins->bind_param("siiii", $_POST['titlename'], $_POST['titletype'], $_POST['titlemaxrank'], $_POST['autofill'], $_POST['gwamm']);
	$stmtins->execute();
	$stmtins->close();
	echo 'New title added: <b>' . h($_POST['titlename']) . '</b><br /><br />';
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
		echo '<tr><td>' . $tid . '</td><td>' . h($tname) . '</td><td>' . $ttype . '</td><td>' . $tmr . '</td><td>' . $taf . '</tr></table><br />';
	}
	$stmtview->close();
	echo 'Return to <a href="titlemanager.php" class="navlink">title manager</a>';
}
?>