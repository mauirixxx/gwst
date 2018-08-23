<?php
# this function verifies that the e-mail address passed doesn't contain any illegal characters
function validateEmail($email) {
	return filter_var($email, FILTER_VALIDATE_EMAIL);
}

# this function verifies the desired e-mail address doesn't already exist in the database
function usedEmail($usedemail) {
	$con = mysqli_connect(DATABASE_HOST, DATABASE_USER, DATABASE_PASS, DATABASE_NAME);
	$sqlemailcheck = "SELECT usermail FROM userinfo WHERE userinfo.usermail = '" . $usedemail . "'";
	$results = mysqli_query($con, $sqlemailcheck);
	if (mysqli_num_rows($results) >= 1) {
		echo '<hr><center>This e-mail address is already registered, please click on the forgot password link.<br /><a href="register.php" class="navlink">Please try again!</a><hr><br />';
		include_once ('footer.php');
		exit();
	}
}
	
# this function verifies that a username doesn't already exist in the database
function validateUsername($uname) {
	$con = mysqli_connect(DATABASE_HOST, DATABASE_USER, DATABASE_PASS, DATABASE_NAME);
	$sqlunamecheck = "SELECT username FROM userinfo WHERE userinfo.username = '" . $uname . "'";
	$results = mysqli_query($con, $sqlunamecheck);
	if (mysqli_num_rows($results) >= 1) {
		echo '<center>This username has already been taken, please choose another one<br /><a href="register.php" class="navlink">Please try again!</a><br />';
		include_once ('footer.php');
		exit();
	}
}

####################
# verifying the username doesn't already exist in the database
if (!empty($_POST['username'])) {
	$username = mysqli_real_escape_string($con, $_POST['username']);
	validateUsername($username);
}

####################
# verifying the e-mail address is in a valid format
if (!empty($_POST['useremail'])) {
	$verifyemail = validateEmail($_POST['useremail']);
	if (empty($verifyemail)) {
		echo '<center>This address: ' . $_POST['useremail'] . ' is not a valid e-mail address!<br />Please verify and type it again.<br />';
		include_once ('footer.php');
		exit();
	}
	usedEmail($_POST['useremail']);
}

####################
# verifying passwords match each other
if (!empty($_POST['userpass1'] && $_POST['userpass2'])) {
	if (($_POST['userpass1']) != ($_POST['userpass2'])) {
		echo '<center>The passwords don\'t match!<br />Please try again!';
		include_once ('footer.php');
		exit();
	}
}
?>