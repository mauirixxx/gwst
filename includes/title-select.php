<?php
$stmtview = $con->prepare("SELECT * FROM gwtitles ORDER BY titlename");
$stmtview->execute();
$result = $stmtview->get_result();
while ($row = $result->fetch_assoc()) {
	$tid = $row['titlenameid'];
	$tname = $row['titlename'];
    $tnr = $row['titlemaxrank'];
	echo '<option value="' . $tid . '">' . $tname . ' (' . $tnr . ')</option>';
}
$stmtview->close();
?>