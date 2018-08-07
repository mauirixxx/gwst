<?php
$pagetitle = "Title Editor";
include_once ('header.php');
include_once ('connect.php');
$con = mysqli_connect(DATABASE_HOST, DATABASE_USER, DATABASE_PASS, DATABASE_NAME);
if ($con->connect_errno){
	die ('Unable to connect to database [' . $db->connect_errno . ']');
}
if (isset($_SESSION['title'])) {
	if ($_SESSION['title'] == "repeat") {
		$_POST['title'] = "addsubtitle";
		unset($_SESSION['title']);
	} else {
		unset($_SESSION['tr']);
	}
}
if ($_SESSION['admin'] == 1) {
	echo '<br />';
	if (isset($_POST['title'])) {
		if ($_POST['title'] == "addtitle") {
			// included file contains all the code to add a new title
			include_once ('includes/title-add.php');
		} else if ($_POST['title'] == "titlesubmit") {
			// included file contains all the code to submit a new title
			include_once ('includes/title-submit.php');
		} else if ($_POST['title'] == "modtitle") { 
			// included file contains all the code to edit a title
			include_once ('includes/title-editor.php');
		} else if ($_POST['title'] == "updatetitle") {
			// this section doesn't require human interaction
			include_once ('includes/title-update.php');
		} else if ($_POST['title'] == "addsubtitle") {
			// included file contains all code to add the title ranks and points required
			include_once ('includes/titleranks-add.php');
		} else if ($_POST['title'] == "titleranksubmit") {
			// this section doesn't require human interaction
			include_once ('includes/titleranks-submit.php');
		} else if ($_POST['title'] == "modsubtitle") {
			// included file contains all code to edit a title rank
			include_once ('includes/titleranks-editor.php');
		} else if ($_POST['title'] == "updatesubtitle") {
			// this sectionupdates modified title ranks in the database, or deletes them
			include_once ('includes/titleranks-update.php');
		}
	} else {
		unset($_SESSION['tid']);
		unset($_SESSION['tr']);
		echo 'Add titles <form action="titlemanager.php" method="post"><input type="hidden" name="title" value="addtitle"><input type="submit" value="Add title"></form><br />';
		echo 'Modify titles <form action="titlemanager.php" method="post"><input type="hidden" name="title" value="modtitle"><select name="tid" onchange="this.form.submit()"><option selected disabled>Select title</option>';
		include ('includes/title-select.php');
		echo '</select><noscript><input type="submit" value="Modify Title"></noscript></form><br /><br />';
		echo 'Add title ranks and points to <form action="titlemanager.php" method="post"><input type="hidden" name="title" value="addsubtitle"><select name="tid" onchange="this.form.submit()"><option selected disabled>Add title rank(s)</option>';
        include ('includes/title-select.php');
        echo '</select><noscript><input type="submit" value="Add title rank"></noscript></form><br /><br />';
		// now to view the last 5 title entries in the database
		echo 'Here is the last 15 titles entered into the database, newest entry is on top:<br />';
		echo '<table border="1"><tr><th>titleid</th><th>titlename</th><th>titletype</th><th>titletype</th></tr>';
		$stmtview = $con->prepare("SELECT * FROM gwtitles ORDER BY titlenameid DESC LIMIT 15");
		$stmtview->execute();
		$result = $stmtview->get_result();
		while ($row = $result->fetch_assoc()) {
			$tid = $row['titlenameid'];
			$tname = $row['titlename'];
			$ttype = $row['titletype'];
            $tmr = $row['titlemaxrank'];
			echo '<tr><td>' . $tid . '</td><td>' . $tname . ' (' . $tmr . ')</td><td>' . $ttype . '</td><td>';
			if ($ttype == "0") {
				echo 'account';
			} else if ($ttype == "1") {
				echo 'character';
			} else {
				echo 'Anything other than a 0 or 1 means something broke!';
				include_once ('footer.php');
				exit();
			}
			echo '</td></tr>';
		}
		$stmtview->close();
		echo '</table><br />If anything looks off, please fix it!<br /><br />';
	}
}
include_once ('footer.php');
?>