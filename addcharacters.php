<?php
$pagetitle = "Add Characters";
include_once ('header.php');
echo 'add a new character!';
echo '<form action="addaccounts.php" method="post"><table>';
echo '<caption style="white-space: nowrap; overflow: hidden;">Add character to account: ' . $_SESSION['prefaccname'] . '</caption>';
echo '<tr><td><input type="text" name="newchar" size="19" required></td><td><input type="submit" value="Add character"></td></tr>';
echo '</table></form><br />';
include_once ('footer.php');
?>