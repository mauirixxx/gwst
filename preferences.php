<?php
$pagetitle = "Account options";
include_once ('header.php');

if (!empty($_POST['useremail'])) {
	//this section contains code to update the users e-mail address
	include_once ('includes/update-email.php');
}

if (!empty($_POST['oldpass'])) {
	// this section contains code to update the users password after verifying the old password first
	include_once ('includes/update-password.php');
}

if (!empty($_POST['setacc'])) {
    //this section contains code to set the users preferred game account
    include_once ('includes/set-prefacc.php');
}
echo '<h3>Change e-mail or password</h3>';

// select which GW account you want to default to
echo '<form action="preferences.php" method="post"><table border="1"><caption style="white-space: nowrap; overflow: hidden;">Current preferred account: <b>' .$_SESSION['prefaccname'] . '</b></caption>';
echo '<tr><td><select name="prefaccid">';
echo '<option value="nopref">Prefer no deafult</option>';
$prefacc = $con->prepare("SELECT accid, accemail FROM gwaccounts WHERE userid = ?");
$prefacc->bind_param("i", $_SESSION['userid']);
$prefacc->execute();
$resacc = $prefacc->get_result();
while ($row = $resacc->fetch_assoc()) {
    echo '<option value="' . $row['accid'] . '">' . $row['accemail'] . '</option>';
}
echo '</td><td><input type="submit" value="Set account"></td></tr></select></table><input type="hidden" name="setacc" value="update"></form><br />';

// select which character from your GW account you want to default to
# needed code: select charrid from table gwchars selected by accid

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
<input type="submit" name="submission" value="Update password" onclick="return Validate()" id="btnSubmit"></form>
UPDPASS;
include_once ('footer.php');
?>