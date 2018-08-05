<?php
$stmtview = $con->prepare("SELECT * FROM gwtitles ORDER BY titlename");
$stmtview->execute();
$result = $stmtview->get_result();
while ($row = $result->fetch_assoc()) {
	$tid = $row['titlenameid'];
	$tname = $row['titlename'];
	echo '<option value="' . $tid . '">' . $tname . '</option>';
}
$stmtview->close();
?>