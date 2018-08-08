<?php

# delete this block when shit finally works.
ini_set('display_errors', 'on');
error_reporting(E_ALL);
mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);
# delete the above when shit finally works

if (isset($_POST['delsubtitle'])) {
	if ($_POST['delsubtitle'] =="yes") {
		// this title makes you verify that you want to delete this title
		echo '<form action="titlemanager.php" method="post">Please check the box to verify you want to delete: <b>' . $_POST['titlename'] . '</b> <input type="checkbox" name="delsubtitle" value="iamsure">';
		echo '<input type="hidden" name="titlenameid" value="' . $_POST['titlenameid'] . '"><input type="hidden" name="title" value="updatesubtitle"><input type="submit" value="Delete title rank(s)"></form><br /><br />';
	} else if ($_POST['delsubtitle'] == "iamsure") {
		// this section actually deletes the title rank(s)
		# need to deal with array data eventually
		$stmtdel = $con->prepare("DELETE FROM gwsubtitles WHERE titlenameid = ?");
		$stmtdel->bind_param("i", $_POST['titlenameid']);
		$stmtdel->execute();
		$stmtdel->close();
		echo 'Title rank(s) have been deleted, redirecting!';
		header ("Refresh:1; url=titlemanager.php");
	}
} else {
	// this section updates the title name
	if ($upd = $con->prepare("UPDATE gwsubtitles SET stname = ?, stpoints = ?, strank = ? WHERE titlenameid = ? AND stnameid = ?")) {
		$upd->bind_param("siiii", $stname, $stpoints, $strank, $titlenameid, $stnameid);
		for ($i = 0; $i < count($_POST['stname']); $i++) {
			$stname = $_POST['stname'][$i];
			$stpoints = $_POST['stpoints'][$i];
			$strank = $_POST['strank'][$i];
			$titlenameid = $_POST['titlenameid'][$i];
			$stnameid = $_POST['stnameid'][$i];
			$upd->execute();
		}
		$upd->close();
	}
	echo 'Title rank(s) updated, redirecting!';
	header ("Refresh:1; url=titlemanager.php");
}
echo 'Return to <a href="titlemanager.php" class="navlink">title manager</a>';
?>