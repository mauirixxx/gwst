<?php
$pagetitle = "Add a Guild Wars account to track";
include_once ('header.php');

if (!empty($_POST['accemail'])) {
	$addacc = $con->prepare("INSERT INTO gwaccounts (userid, accemail) VALUES (?, ?)");
	$addacc->bind_param("is", $_SESSION['userid'], $_POST['accemail']);
	$addacc->execute();
	$addacc->close();
	echo 'New account added, returning to editor.';
	header ("Refresh:1; url=addaccounts.php");
	exit();
}

if (!empty($_POST['delchar'])) {
    echo 'removing selected character(s) from selected account<br />';
    $delchar = $con->prepare("DELETE FROM gwchars WHERE charid = ? AND accid = ? AND userid = ?");
    $delchar->bind_param("iii", $_POST['charid'], $_POST['accid'], $_SESSION['userid']);
    $delchar->execute();
    $delchar->close();
    echo 'Need to figure out code to delete stats related to this character still!<br />';
    //header ("Refresh:1; url=addacounts.php");
    //exit();
}

echo '<form action="addaccounts.php" method="post"><table>';
echo '<caption>Add a new Guild Wars account e-mail or alias</caption>';
echo '<tr><td><input type="text" name="accemail" size="35" required></td><td><input type="submit" value="Add account"></td></tr>';
echo '</table></form><br />';

echo '<table border="1"><caption style="white-space: nowrap; overflow: hidden;">Current Guild Wars accounts</caption>';
echo '<tr><th>Account name</th></tr>';
// grab account name from database and loop it in here as a read only bit
$acclist = $con->prepare("SELECT accid, accemail FROM gwaccounts WHERE userid = ?");
$acclist->bind_param("i", $_SESSION['userid']);
$acclist->execute();
$result = $acclist->get_result();
while ($row = $result->fetch_assoc()) {
    echo '<tr><td>';
	if ($row['accid'] == $_SESSION['prefaccid']) {
		echo '<b>' . $row['accemail'] . '</b>';
	} else {
		echo $row['accemail'];
	}
	echo '</td></tr>';
}
$acclist->close();
echo '</table><br />';

echo '<form action="addaccount.php" method="post"><table border="1"><caption style="white-space: nowrap; overflow: hidden;">Available characters</caption>';
echo '<tr><td>charid</td><td>charname</td><td>Delete?</td></tr>';
$lc = $con->prepare("SELECT charid, charname FROM gwchars WHERE accid = ?");
$lc->bind_param("i", $_SESSION['prefaccid']);
$lc->execute();
$res2 = $lc->get_result();
while ($row2 = $res2->fetch_assoc()) {
	echo '<tr><td>' . $row2['charid'] . '</td><td>';
	if ($row2['charid'] == $_SESSION['prefcharid']) {
		echo '<b>' . $row2['charname'] . '</b>';
	} else {
		echo $row2['charname'];
	}
	echo '</td><td><input type="checkbox" name="delchar" value="yes"></td></tr>';
}
echo '</table>';
echo '<br />Return to your <a href="index.php" class="navlink">user</a> page';
include_once ('footer.php');
?>