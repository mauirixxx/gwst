<?php
$pagetitle = "Update account wide stats";
include_once ('header.php');
if (isset($_SESSION['userid'])) {
    include_once ('includes/session-debug.php');
    include_once ('includes/session-dump.php');
    echo 'Current account stats for: <b>' . $_SESSION['prefaccname'] . '</b><br />';
    include_once ('includes/getaccountstats.php');
    echo 'Return to your <a href="index.php" class="navlink">user</a> page';
}
include_once ('footer.php');
?>