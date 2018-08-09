<?php
$pagetitle = "Account options";
include_once ('header.php');

if (!empty($_POST['useremail'])) {
	//this section contains code to update the users e-mail address, maybe via an include?
	include_once ('includes/update-email.php');
}

if (!empty($_POST['oldpass'])) {
	echo 'if the oldpass is set, execute code here (this line will go away from preferences.php!)<br />';
	include_once ('includes/update-password.php');
} else {
	echo 'if post oldpass is NOT set, then this else statement can go away.<br />';
}

echo '<h3>Change e-mail or password</h3>';

// listing all session variables currently set for debugging purposes
echo '<pre>';
var_dump($_SESSION);
echo '</pre><br />';
// delete the above 3 lines when done

// update e-mail address form
echo '<form action="preferences.php" method="post"><table border="1">';
echo '<caption>Update e-mail address</caption>';
echo '<tr><td><input type="text" name="useremail" value="' . $_SESSION['usermail'] . '"></td><td><input type="submit" value="Update e-mail"></td></tr>';
echo '</table></form><br /><br />';

// update password form
echo <<<UPDPASS
<form action="preferences.php" method="post"><table border="1">
<tr><th>Old Password</th><tr>
<tr><td><input type="password" name="oldpass" required></td></tr>
<tr><th>New password</th></tr>
<tr><td><input type="password" required="required" name="userpass1" id="up1"></td></tr>
<tr><th>Verify password</th></tr>
<tr><td><input type="password" required="required" name="userpass2" id="up2"></td></tr>
</table><script type="text/javascript">
    function Validate() {
        var userpass1 = document.getElementById("up1").value;
        var userpass2 = document.getElementById("up2").value;
        if (userpass1 != userpass2) {
            alert("Passwords do not match.");
            return false;
        }
        return true;
    }
</script>
<input type="submit" name="submission" value="Update password" onclick="return Validate()" id="btnSubmit">
UPDPASS;
include_once ('footer.php');
?>