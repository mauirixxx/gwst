<?php
# delete this block when shit finally works.
ini_set('display_errors', 'on');
error_reporting(E_ALL);
mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);
# delete the above when shit finally works

# use "$_POST['tid']" to show title ranks already associated with the selected title
# and above that list put the input boxes for text and number_format
# investigate if we can put multiple input boxes and loop through them to insert into the database
# https://stackoverflow.com/questions/34469482/how-to-insert-multiple-inputs-into-the-database-using-the-power-of-php

if (isset($_SESSION['tid'])) {
	$_POST['tid'] = $_SESSION['tid'];
}

$stmtname = $con->prepare("SELECT titlename, titlemaxrank FROM gwtitles WHERE titlenameid = ?");
$stmtname->bind_param("i", $_POST['tid']);
$stmtname->execute();
$stmtname->store_result();
$stmtname->bind_result($gwtn, $gwtmr);
while ($stmtname->fetch()) {
    echo 'Adding rank to title <b>' . $gwtn . '</b><br />It\'s current maximum rank is ' . $gwtmr . '<br />';
}
$stmtname->free_result();
$stmtname->close();

echo '<form action="titlemanager.php" method="post"><table border="1"><tr><th>Title Rank Name</th><th>Title Points</th><th>Rank Level</th></tr>';
echo '<tr><td><input type="text" name="titlerankname" required autofocus></td><td><input type="number" name="titlepoints" required></td><td><input type="number" name="titlerank" min="1" max="15"</tr>';
echo '</table><br /><input type="hidden" name="title" value="titleranksubmit"><input type="hidden" name="titlenameid" value="' . $_POST['tid'] . '"><input type="submit" value="Add title rank ..."></form>';

echo 'Here are the currently associated title ranks, starting with rank 1:<br />';
echo '<table border="1"><tr><th>stnameid</th><th>titlenameid</th><th>stname</th><th>stpoints</th><th>strank</th></tr>';
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
	echo '<tr><td>' . $stnid . '<td>' . $tnid . '</td><td>' . $stname . '</td><td>' . number_format($stpoints) . '</td><td>' . $strank . '</td></tr>';
}
$stmtview->close();
echo '</table><br />If anything looks off, please fix it!<br /><br />';
echo 'Return to <a href="titlemanager.php" class="navlink">title manager</a>'; //this line needs to go away soon. Maybe?
?>