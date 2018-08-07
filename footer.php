</center>
<?php
// the footer just adds a logout button at the bottom of every page for the currently logged in user
if (isset($_SESSION['userid']) && ($_SESSION['username'])) {
	echo '<center><br /><br /><form method="post" action="logout.php"><input type="hidden" name="action" value="logout" ><input type="submit" value="Logout"></form></center>';
}
?>
</body>
</html>