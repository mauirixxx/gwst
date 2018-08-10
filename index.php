<!-- this is the main directory of the site, which links to the various content pages -->
<?php
$pagetitle = "Guild Wars Stats Tracker";
include_once ('header.php');
if (isset($_SESSION['userid'])){
	echo 'View individual character stats <A HREF="listchars.php" class="navlink">here</A><BR /><BR />'; //make this a drop down list later
	echo 'Update character stats <A HREF="updatecharstats.php" class="navlink">here</A><BR /><BR />';
	echo 'Update account stats <A HREF="updateaccountstats.php" class="navlink">here</A><BR /><BR />';
	echo 'Add Guild Wars accounts and manage characters <a href="addaccounts.php" class="navlink">here</a><br /><br />';
    echo 'Add a <a href="addcharacters.php" class="navlink">new character</a> to track<br />';
}
include_once ('footer.php');
?>