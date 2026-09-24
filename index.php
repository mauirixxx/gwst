<!-- this is the main directory of the site, which links to the various content pages -->
<?php
$pagetitle = "Guild Wars Titles & Treasures Tracker";
include_once ('header.php');
if (isset($_SESSION['userid'])){
	include_once ('includes/current-character.php');
}
include_once ('footer.php');
?>