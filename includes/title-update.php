<?php
if (isset($_SESSION['userid'])) {
	if (isset($_POST['deltitle'])) {
		if ($_POST['deltitle'] == "yes") {
			// this title makes you verify that you want to delete this title
			echo '<form action="titlemanager.php" method="post">Please check the box to verify you want to delete: <b>' . $_POST['titlename'] . '</b> <input type="checkbox" name="deltitle" value="iamsure">';
			echo '<input type="hidden" name="titlenameid" value="' . $_POST['titlenameid'] . '"><input type="hidden" name="title" value="updatetitle"><input type="submit" value="Delete title"></form><br /><br />';
		} else if ($_POST['deltitle'] == "iamsure") {
			// this section actually deletes the title
			$stmtdel = $con->prepare("DELETE FROM gwtitles WHERE titlenameid = ?");
			$stmtdel->bind_param("i", $_POST['titlenameid']);
			$stmtdel->execute();
			$stmtdelst = $con->prepare("DELETE FROM gwsubtitles WHERE titlenameid = ?");
			$stmtdelst->bind_param("i", $_POST['titlenameid']);
			$stmtdelst->execute();
			$stmtdel->close();
			echo 'The title and associated title ranks have been deleted, redirecting!';
			header ("Refresh:1; url=titlemanager.php");
		}
	} else {
		if (!isset($_POST['autofill'])) {
			$_POST['autofill'] == 0;
		}
		if (!isset($_POST['gwamm'])) {
			$_POST['gwamm'] == 0;
		} else {
			// $ggid = Get Gwamm ID from current GWAMM holder
			$ggid = $con->prepare("SELECT titlenameid FROM gwtitles WHERE gwamm = 1");
			$ggid->execute();
			$ggid->bind_result($gwammid);
			$ggid->fetch();
			$ggid->close();
			// $rg = Remove GWAMM
			$rg = $con->prepare("UPDATE gwtitles SET gwamm = 0 WHERE titlenameid = ?");
			$rg->bind_param("i", $gwammid);
			$rg->execute();
			$rg->close();
		}
		// this section updates the title name
		$stmtupd = $con->prepare("UPDATE gwtitles SET titlename = ?, titletype = ?, titlemaxrank = ?, autofilled = ?, gwamm = ? WHERE titlenameid = ?");
		$stmtupd->bind_param("siiiii", $_POST['titlename'], $_POST['titletype'], $_POST['titlemaxrank'], $_POST['autofill'], $_POST['gwamm'], $_POST['titlenameid']);
		$stmtupd->execute();
		$stmtupd->close();
		echo 'Title updated, redirecting!';
		header ("Refresh:1; url=titlemanager.php");
	}
}
?>