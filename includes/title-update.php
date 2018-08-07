<?php
if (isset($_POST['deltitle'])) {
	if ($_POST['deltitle'] =="yes") {
		// this title makes you verify that you want to delete this title
		echo '<form action="titlemanager.php" method="post">Please check the box to verify you want to delete: <b>' . $_POST['titlename'] . '</b> <input type="checkbox" name="deltitle" value="iamsure">';
		echo '<input type="hidden" name="titlenameid" value="' . $_POST['titlenameid'] . '"><input type="hidden" name="title" value="updatetitle"><input type="submit" value="Delete title"></form><br /><br />';
	} else if ($_POST['deltitle'] == "iamsure") {
		// this section actually deletes the title
		$stmtdel = $con->prepare("DELETE FROM gwtitles WHERE titlenameid = ?");
		$stmtdel->bind_param("i", $_POST['titlenameid']);
		$stmtdel->execute();
		$stmtdel->close();
		echo 'Title has been deleted, redirecting!';
		header ("Refresh:1; url=titlemanager.php");
	}
} else {
	// this section updates the title name
	$stmtupd = $con->prepare("UPDATE gwtitles SET titlename = ?, titletype = ?, titlemaxrank = ? WHERE titlenameid = ?");
	$stmtupd->bind_param("siii", $_POST['titlename'], $_POST['titletype'], $_POST['titlemaxrank'], $_POST['titlenameid']);
	$stmtupd->execute();
	$stmtupd->close();
	echo 'Title updated, redirecting!';
	header ("Refresh:1; url=titlemanager.php");
}
?>