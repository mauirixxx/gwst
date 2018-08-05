<?php
$stmtins = $con->prepare("INSERT INTO gwtitles (titlename, titletype) VALUES (?, ?)");
$stmtins->bind_param("si", $_POST['titlename'], $_POST['titletype']);
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
	echo '<table border="1"><tr><th>titleid</th><th>titlename</th><th>titletype</th></tr>';
	echo '<tr><td>' . $tid . '</td><td>' . $tname . '</td><td>' . $ttype . '</td></tr></table><br />';
}
$stmtview->close();
echo 'Return to <a href="titlemanager.php" class="navlink">title manager</a>';
?>