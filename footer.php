</center>
<?php
echo '<hr>';
if (isset($_SESSION['prefaccname']) && ($_SESSION['prefcharname'])) {
	echo '<center>| Currently selected game account: <b>' . $_SESSION['prefaccname'] . '</b> | Current character: <b>' . $_SESSION['prefcharname'] . '</b> |</center><br />';
}
// the footer just adds a logout button at the bottom of every page for the currently logged in user
if (isset($_SESSION['userid']) && ($_SESSION['username'])) {
	echo '<center><br /><br /><form method="post" action="logout.php"><input type="hidden" name="action" value="logout" ><input type="submit" value="Logout"></form></center>';
}
if (isset($_SESSION['userid'])) {
	$csrf_token_json = json_encode(csrf_token());
	echo '<script>
	(function () {
		const token = ' . $csrf_token_json . ';
		document.querySelectorAll("form").forEach(function (form) {
			if ((form.method || "").toLowerCase() !== "post" || form.querySelector("input[name=csrf_token]")) {
				return;
			}
			const input = document.createElement("input");
			input.type = "hidden";
			input.name = "csrf_token";
			input.value = token;
			form.appendChild(input);
		});
	})();
	</script>';
}
?>
</body>
</html>