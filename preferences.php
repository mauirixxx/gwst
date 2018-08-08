<?php
$pagetitle = "Account options";
include_once ('header.php');
if (!empty($_POST['usermail'])) {
	//this section contains code to update the users e-mail address, maybe via an include?
	include_once ('includes/update-email.php');
} else {
	//should get rid of this whole else statement, or use it to display an error?
	echo 'this should probably be the !empty section instead<br />';
	echo 'the post usermail vaiable should be blank: <b>' . $_POST['usermail'] . '</b> <- Is there a blank spot to the left?<br />';
}
echo '<h3>Change e-mail and / or password</h3><br /><br />';

// listing all session variables currently set for debugging purposes
echo '<pre>';
var_dump($_SESSION);
echo '</pre><br /><br />';
// delete the above 3 lines when done

echo '<form action="preferences.php" method="post"><table border="1">';
echo '<caption>Update e-mail address</caption>';
echo '<tr><td><input type="text" name="usermail" value="' . $_SESSION['usermail'] . '"></td><td><input type="submit" value="Update e-mail"></td></tr>';
echo '</table></form><br />';
include_once ('footer.php');
?>