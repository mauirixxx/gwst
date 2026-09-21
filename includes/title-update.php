<?php
if (isset($_SESSION['userid']) && isset($_SESSION['admin']) && $_SESSION['admin'] == 1) {
	if (isset($_POST['deltitle'])) {
		if ($_POST['deltitle'] == "yes") {
			// this section makes you verify that you really want to delete this title
			echo '<form action="titlemanager.php" method="post">Please check the box to verify you want to delete: <b>' . $_POST['titlename'] . '</b> <input type="checkbox" name="deltitle" value="iamsure">';
			echo '<input type="hidden" name="titlenameid" value="' . $_POST['titlenameid'] . '"><input type="hidden" name="title" value="updatetitle"><input type="submit" value="Delete title"></form><br /><br />';
		} else if ($_POST['deltitle'] == "iamsure") {
			$titlenameid = (int)$_POST['titlenameid'];
			$stmtname = $con->prepare("SELECT titlename FROM gwtitles WHERE titlenameid = ?");
			$stmtname->bind_param("i", $titlenameid);
			$stmtname->execute();
			$stmtname->bind_result($deleted_title_name);
			$stmtname->fetch();
			$stmtname->close();
			// Delete the title and all dependent stats/ranks atomically.
			$con->begin_transaction();
			try {
				$stmtdelstats = $con->prepare("DELETE FROM gwstats WHERE titlenameid = ?");
				$stmtdelstats->bind_param("i", $titlenameid);
				$stmtdelstats->execute();
				$stmtdelstats->close();

				$stmtdelst = $con->prepare("DELETE FROM gwsubtitles WHERE titlenameid = ?");
				$stmtdelst->bind_param("i", $titlenameid);
				$stmtdelst->execute();
				$stmtdelst->close();

				$stmtdel = $con->prepare("DELETE FROM gwtitles WHERE titlenameid = ?");
				$stmtdel->bind_param("i", $titlenameid);
				$stmtdel->execute();
				$stmtdel->close();

				$con->commit();
			} catch (Throwable $e) {
				$con->rollback();
				throw $e;
			}
			echo 'Deleted title <b>' . h($deleted_title_name) . '</b>, including its associated ranks and assigned title stats. Redirecting!';
			header ("Refresh:1; url=titlemanager.php");
		}
	} else {
		if (!isset($_POST['autofill'])) {
			$_POST['autofill'] = 0;
		}
		if (!isset($_POST['gwamm'])) {
			$_POST['gwamm'] = 0;
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
		echo 'Title updated: <b>' . h($_POST['titlename']) . '</b>. Redirecting!';
		header ("Refresh:1; url=titlemanager.php");
	}
}
?>