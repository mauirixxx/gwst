<?php
$pagetitle = "Guild Wars Stats Tracker";
include_once ('header.php');
if (isset($_SESSION['userid'])){
	echo 'Update character stats <A HREF="updatecharstats.php" CLASS="navlink">here</A><BR /><BR />';
	echo 'Update account stats <A HREF="updateaccountstats.php" CLASS="navlink">here</A><BR /><BR />';
	echo 'View individual character stats <A HREF="listchars.php" CLASS="navlink">here</A><BR /><BR />'; //make this a drop down list later
	echo 'Change Guild Wars account <A HREF="changeaccounts.php" CLASS="navlink">here</A><BR />'; //make this a drop down list later
    echo 'Add Guild Wars account <a href="addaccounts.php" class="navlink">here</a><br />';
}
include_once ('footer.php');
?>