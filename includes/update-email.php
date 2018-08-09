<?php
include_once ('verifications.php');
$updmail = $con->prepare("UPDATE userinfo SET usermail = ? WHERE userid = ?");
$updmail->bind_param("si", $_POST['useremail'], $_SESSION['userid']);
$updmail->execute();
$_SESSION['usermail'] = $_POST['useremail'];
echo 'E-mail address updated.<br />';
?>