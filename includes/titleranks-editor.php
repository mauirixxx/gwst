<?php

# delete this block when shit finally works.
ini_set('display_errors', 'on');
error_reporting(E_ALL);
mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);
# delete the above when shit finally works

$tid = $_SESSION['tid'];
echo 'the session tid number is: <b>' . $_SESSION['tid'] . '</b><br />';

$stid = array();
foreach ($_POST['editstitle'] as $tredit) {
	$stid[] = (int)$tredit;
}
$stid = implode(',', $stid);
echo 'the value of stid is now: ' . $stid . '<br />';
$sredit = $con->prepare("SELECT * FROM gwsubtitles WHERE titlenameid = ? AND stnameid IN (?)");
$sredit->bind_param("is", $tid, $stid);
$sredit->execute();
$sredit->store_result();
$sredit->bind_result($gwtid, $gwstid);
while ($sredit->fetch()) {
	echo 'farts <br />';
}
$sredit->free_result();
$sredit->close();

// keep this section
/* if (isset($_POST['editstitle'])) {
	$sredit = $con->prepare("SELECT * FROM gwsubtitles WHERE stnameid = ? AND titlenameid = ?");
	foreach ($_POST['editstitle'] as $tredit) {
		$sredit->bind_param("ii", $tid, $tredit);
		$sredit->execute();
		$sredit->store_result();
		$sredit->bindresult(
		echo 'the value is: <b>' . $tredit . '</b><br />';
	}
} else {
	echo 'nothing is set?? <br />';
}
*/
echo '<br />code to mod title ranks via an include goes here<br /><br />';
echo 'Return to <a href="titlemanager.php" class="navlink">title manager</a>';
?>