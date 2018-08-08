<?php

# delete this block when shit finally works.
ini_set('display_errors', 'on');
error_reporting(E_ALL);
mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);
# delete the above when shit finally works

echo '<form action="titlemanager.php" method="post"><table border="1"><tr><th>stnameid</th><th>titlenameid</th><th>stname</th><th>stpoints</th><th>strank</th></tr>'; //save this line!!!
$ph = implode(",", $_POST['editstitle']);
$sredit = $con->prepare("SELECT * FROM gwsubtitles WHERE titlenameid = ? AND stnameid IN ($ph)");
$sredit->bind_param("i", $_SESSION['tid']);
$sredit->execute();
$result = $sredit->get_result();
while ($row = $result->fetch_assoc()) {
	echo '<tr><td><input type="text" readonly size="4" name="stnameid[]" value="' . $row['stnameid']. '"></td><td><input type="text" readonly size="4" name="titlenameid[]" value="' . $row['titlenameid'] . '"></td>';
	echo '<td><input type="text" name="stname[]" value="' . $row['stname'] . '"></td><td><input type="number" min="1" name="stpoints[]" value="' . $row['stpoints'] . '"></td>';
	echo '<td><input type="number" size="4" min="1" max="15" name="strank[]" value="' . $row['strank'] . '"></td></tr>';
}
echo '</table><br /><input type="hidden" name="title" value="updatesubtitle"><input type="submit" value="Modify title rank(s)"></form>';
echo '<br /><br />';
echo 'Return to <a href="titlemanager.php" class="navlink">title manager</a>';
?>