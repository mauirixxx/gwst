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

echo '<form action="addaccounts.php" method="post"><table>';
echo '<caption>Add a new Guild Wars account e-mail</caption>';
echo '<tr><td><input type="text" name="accemail" size="35" autofocus required></td></tr>';
echo '</table><input type="submit" value="Add account"></form><br /><br />';

echo '<table border="1"><caption style="white-space: nowrap; overflow: hidden;">Current Guild Wars accounts</caption>';
echo '<tr><th>Account name</th></tr>';
// grab account name from database and loop it in here as a read only bit
$acclist = $con->prepare("SELECT accemail FROM gwaccounts WHERE userid = ?");
$acclist->bind_param("i", $_SESSION['userid']);
$acclist->execute();
$result = $acclist->get_result();
while ($row = $result->fetch_assoc()) {
    echo '<tr><td>' . $row['accemail'] . '</td></tr>';
}
$acclist->close();

echo '</table>';
echo '<br />Return to your <a href="index.php" class="navlink">user</a> page';
include_once ('footer.php');
?>