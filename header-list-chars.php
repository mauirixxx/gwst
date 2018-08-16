<?php
if (isset($_SESSION['userid'])) {
	// $cls = Character List Select
	$cls = $con->prepare("SELECT charid, charname, profid FROM gwchars WHERE accid = ? AND userid = ? ORDER BY charname");
	$cls->bind_param("ii", $_SESSION['prefaccid'], $_SESSION['userid']);
	$cls->execute();
	$clsres = $cls->get_result();
	while ($clsrow = $clsres->fetch_assoc()) {
		echo '<option class="profession-' . $clsrow['profid'] . '" value="' . $clsrow['charid'] . '">' . $clsrow['charname'] . '</option>';
	}
	$cls->close();
}
?>