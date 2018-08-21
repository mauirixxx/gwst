<?php
if (isset($_SESSION['userid'])) {
	echo '<form action="titlemanager.php" method="post">';
	echo '<table border="1"><tr><th>titlenameid</th><th>titlename</th><th>titletype</th><th>titlemaxrank</th><th>autofilled</th><th>gwamm</th></tr>';
	$stmtview = $con->prepare("SELECT * FROM gwtitles WHERE titlenameid = ?");
	$stmtview->bind_param("i", $_POST['tid']);
	$stmtview->execute();
	$result = $stmtview->get_result();
	while ($row = $result->fetch_assoc()) {
		$tid = $row['titlenameid'];
		$tname = $row['titlename'];
		$ttype = $row['titletype'];
		$tmr = $row['titlemaxrank'];
		$taf = $row['autofilled'];
		$tg = $row['gwamm']; // $tg = Title GWAMM tracking
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
		echo 'value="1">Character</td><td><input type="number" name="titlemaxrank" min="1" max="15" value="' . $tmr . '"></td><td>';
		echo '<input type="checkbox" name="autofill" value="1" ';
		if ($taf == 1) {
			echo 'checked';
		}
		echo '></td><td><input type="checkbox" name="gwamm" value="1" ';
		if ($tg == 1) {
			echo 'checked';
		}
		echo '></td></tr>';
	}
	$stmtview->close();
	echo '</table><table><tr><td>The current GWAMM title is: <b>';
	// $ggt = Get GWAMM Title
	$ggt = $con->prepare("SELECT titlename FROM gwtitles WHERE gwamm = '1'");
	$ggt->execute();
	$ggt->bind_result($gwamm);
	$ggt->fetch();
	$ggt->close();
	echo $gwamm . '</b></td></tr>';
	echo '<tr><th>Delete title?</th></tr><tr><td><input type="checkbox" name="deltitle" value="yes"></td></tr></table><br /><br />';
	echo '<input type="hidden" name="title" value="updatetitle"><input type="submit" value="Modify title ..."></form><br />';
	echo 'Return to <a href="titlemanager.php" class="navlink">title manager</a>';
}
?>