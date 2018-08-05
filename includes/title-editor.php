<?php
echo '<form action="titlemanager.php" method="post">';
echo '<table border="1"><tr><th>titlenameid</th><th>titlename</th><th>titletype</th></tr>';
$stmtview = $con->prepare("SELECT * FROM gwtitles WHERE titlenameid = ?");
$stmtview->bind_param("i", $_POST['tid']);
$stmtview->execute();
$result = $stmtview->get_result();
while ($row = $result->fetch_assoc()) {
	$tid = $row['titlenameid'];
	$tname = $row['titlename'];
	$ttype = $row['titletype'];
	echo '<tr><td><input readonly size="3" name="titlenameid" value="' . $tid . '"></td><td><input size="40" type="text" name="titlename" value="' . $tname . '"></td><td style="text-align:left">';
	echo '<input type="radio" name="titletype" ';
	if ($ttype == 0) {
		echo 'checked ';
	}
	echo 'value="0">Account<br />';	
	echo '<input type="radio" name="titletype" ';
	if ($ttype == 1) {
		echo 'checked ';
	} 
	echo 'value="1">Character</td></tr>';
}
$stmtview->close();
echo '</table><table><tr><th>Delete title?</th></tr><tr><td><input type="checkbox" name="deltitle" value="yes"></td></tr></table><br /><br />';
echo '<input type="hidden" name="title" value="updatetitle"><input type="submit" value="Modify title ..."></form><br />';
echo 'Return to <a href="titlemanager.php" class="navlink">title manager</a>';
?>