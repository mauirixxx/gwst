<?php
include_once ('verifications.php');
echo 'the useremail variable is: ' . $_POST['useremail'] . '<br />';
echo 'the userid variable is: ' . $_SESSION['userid'] . '<br />';
$updmail = $con->prepare("UPDATE userinfo SET usermail = ? WHERE userid = ?");
$updmail->bind_param("si", $_POST['useremail'], $_SESSION['userid']);
$updmail->execute();
$_SESSION['usermail'] = $_POST['useremail'];
echo 'Email address updated, redirecting.';
header ("Refresh:2; url=preferences.php");
?>