<form action="register.php" method="post">
<table border="1">
<tr><th colspan="2">Username desired</th></tr>
<tr><td colspan="2"><input type="text" size="45" required="required" name="username"></td></tr>
<tr><th colspan="2">E-Mail address</th></tr>
<tr><td colspan="2"><input type="email" size="45" required="required" name="useremail"></td></tr>
<tr><th>Password</th><th>Verify password</th></tr>
<tr><td><input type="password" required="required" name="userpass1" id="up1"></td><td><input type="password" required="required" name="userpass2" id="up2"></td></tr>
</table>
<script type="text/javascript">
    function Validate() {
        var userpass1 = document.getElementById("up1").value;
        var userpass2 = document.getElementById("up2").value;
        if (userpass1 != userpass2) {
            alert("Passwords do not match.");
            return false;
        }
        return true;
    }
</script>
<input type="hidden" name="csrf_token" value="<?php echo h(csrf_token()); ?>">
<input type="hidden" name="reguser" value="1">
<input type="submit" name="submission" value="Go! Go! Go!" onclick="return Validate()" id="btnSubmit">
</form>