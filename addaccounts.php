<?php
$pagetitle = "Add a Guild Wars account to track";
include_once ('header.php');

# delete this block when shit finally works.
ini_set('display_errors', 'on');
error_reporting(E_ALL);
mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);
# delete the above when shit finally works

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
    if ($delchar = $con->prepare("DELETE FROM gwchars WHERE charid = ? AND accid = ? AND userid = ?")) {
        $delchar->bind_param("iii", $delcharid, $delaccid, $_SESSION['userid']);
        for ($i = 0; $i < count($_POST['delchar']); $i++) {
            $delcharid = $_POST['charid'][$i];
            $delaccid = $_POST['accid'][$i];
            $delchar->execute();
        }
        $delchar->close();
    }
    $nap = $con->prepare("UPDATE userinfo SET prefcharid = 0, prefcharname = 'No default selected' WHERE userid = ?");
	$nap->bind_param("i", $_SESSION['userid']);
	$nap->execute();
	$nap->close();
	$_SESSION['prefcharid'] = "0";
	$_SESSION['prefcharname'] = "No default selected";
	echo 'Character deleted - no preferred character selected.<br /><br />';
}

if (!empty($_POST['newcharname'])) {
	include_once ('includes/addcharacters-submit.php');
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

// add characters here
echo '<form action="addaccounts.php" method="post"><table>';
echo '<caption style="white-space: nowrap; overflow: hidden;">Add character to account: ' . $_SESSION['prefaccname'] . '</caption>';
echo '<tr><th>Character name</th><th>Birthdate</th><th>Profession</th></tr>';
echo '<tr><td><input type="text" name="newcharname" size="19" required autofocus></td><td><input type="date" name="bdate" placeholder="2005-04-28"></td><td><select name="profid" required>';
// $gp = Get Profession
$gp = $con->prepare("SELECT profid, profession FROM gwprofessions");
$gp->execute();
$result = $gp->get_result();
while ($row = $result->fetch_assoc()) {
	echo '<option value=' . $row['profid'] . '>' . $row['profession'] . '</option>';
}
echo '</td></tr>';
echo '<tr><td colspan="3"><input type="submit" value="Add character"></td></tr></table></form><br />';

echo '<form action="addaccounts.php" method="post"><table border="1"><caption style="white-space: nowrap; overflow: hidden;">Available characters</caption>';
echo '<tr><td>charid</td><td>accid</td><td>charname</td><td>Delete?</td></tr>';
$lc = $con->prepare("SELECT charid, accid, charname FROM gwchars WHERE accid = ?");
$lc->bind_param("i", $_SESSION['prefaccid']);
$lc->execute();
$res2 = $lc->get_result();
while ($row2 = $res2->fetch_assoc()) {
	echo '<tr><td><input type="text" readonly size="4" name="charid[]" value="' . $row2['charid'] . '"></td>';
    echo '<td><input type="text" readonly size="4" name=accid[]" value="' . $row2['accid'] . '"</td><td>';
	if ($row2['charid'] == $_SESSION['prefcharid']) {
		echo '<b>' . $row2['charname'] . '</b>';
	} else {
		echo $row2['charname'];
	}
	echo '</td><td><input type="checkbox" name="delchar[]" value="' . $row2['charid'] . '"></td></tr>';
}
echo '</table><input type="submit" value="Delete selected characters"></form><br />';
echo '<br />Return to your <a href="index.php" class="navlink">user</a> page';
include_once ('footer.php');
?>