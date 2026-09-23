<form action="register.php" method="post" class="auth-form" id="registration-form">
    <div class="auth-field">
        <label for="register-username">Username</label>
        <input id="register-username" type="text" name="username" autocomplete="username" minlength="1" maxlength="30" pattern="[A-Za-z0-9]+" title="Use letters and numbers only, with no spaces." required autofocus>
    </div>

    <div class="auth-field">
        <label for="register-email">E-mail address</label>
        <input id="register-email" type="email" name="useremail" autocomplete="email" maxlength="50" required>
    </div>

    <div class="auth-field">
        <label for="up1">Password</label>
        <input id="up1" type="password" name="userpass1" autocomplete="new-password" minlength="8" maxlength="255" required>
    </div>

    <div class="auth-field">
        <label for="up2">Verify password</label>
        <input id="up2" type="password" name="userpass2" autocomplete="new-password" minlength="8" maxlength="255" required>
    </div>

    <input type="hidden" name="csrf_token" value="<?php echo h(csrf_token()); ?>">
    <input type="hidden" name="reguser" value="1">
    <button type="submit" name="submission" class="auth-primary">Create account</button>
</form>

<script>
document.getElementById('registration-form').addEventListener('submit', function (event) {
    const password = document.getElementById('up1');
    const verify = document.getElementById('up2');

    if (password.value !== verify.value) {
        event.preventDefault();
        verify.setCustomValidity('Passwords do not match.');
        verify.reportValidity();
        verify.focus();
        return;
    }

    verify.setCustomValidity('');
});

document.getElementById('up2').addEventListener('input', function () {
    this.setCustomValidity('');
});
</script>
