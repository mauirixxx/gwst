<?php
$pagetitle = "Account options";
include_once ('header.php');

if (!empty($_POST['useremail'])) {
	//this section contains code to update the users e-mail address, maybe via an include?
	include_once ('includes/update-email.php');
}
echo '<h3>Change e-mail or password</h3>';

// listing all session variables currently set for debugging purposes
echo '<pre>';
var_dump($_SESSION);
echo '</pre><br />';
// delete the above 3 lines when done

echo '<form action="preferences.php" method="post"><table border="1">';
echo '<caption>Update e-mail address</caption>';
echo '<tr><td><input type="text" name="useremail" value="' . $_SESSION['usermail'] . '"></td><td><input type="submit" value="Update e-mail"></td></tr>';
echo '</table></form><br />';
include_once ('footer.php');
?>