<?php
$pagetitle = "Account options";
include_once ('header.php');
if (isset($_SESSION['userid'])){
    $preference_message = '';

    echo '<section class="options-page">';

    if (isset($_POST['save_email_preferences'])) {
        $birthday_email_enabled = isset($_POST['birthday_email_enabled']) ? 1 : 0;
        $birthday_reminder_days = (int)($_POST['birthday_reminder_days'] ?? 0);
        $treasure_email_enabled = isset($_POST['treasure_email_enabled']) ? 1 : 0;
        if (!in_array($birthday_reminder_days, array(0, 1, 3, 7), true)) {
            $birthday_reminder_days = 0;
        }
        $prefstmt = $con->prepare("INSERT INTO user_preferences (userid, birthday_email_enabled, birthday_reminder_days, treasure_email_enabled) VALUES (?, ?, ?, ?) ON DUPLICATE KEY UPDATE birthday_email_enabled = VALUES(birthday_email_enabled), birthday_reminder_days = VALUES(birthday_reminder_days), treasure_email_enabled = VALUES(treasure_email_enabled)");
        $prefstmt->bind_param("iiii", $_SESSION['userid'], $birthday_email_enabled, $birthday_reminder_days, $treasure_email_enabled);
        if ($prefstmt->execute()) {
            $preference_message = 'E-mail reminder preferences updated.';
        } else {
            $preference_message = 'Unable to update e-mail reminder preferences.';
        }
        $prefstmt->close();
    }

    if (!empty($_POST['useremail'])) {
        include_once ('includes/update-email.php');
    }
    if (!empty($_POST['oldpass'])) {
        include_once ('includes/update-password.php');
    }
    if (!empty($_POST['setacc'])) {
        include_once ('includes/set-prefacc.php');
    }
    if (!empty($_POST['setchar'])) {
        include_once ('includes/set-prefchar.php');
    }

    echo '<h3>Set preferred account &amp; character, or change e-mail or password</h3>';

    echo '<fieldset class="options-card account-options-card">';
    echo '<legend>Account &amp; character</legend>';

    echo '<form action="preferences.php" method="post" class="options-form">';
    echo '<div class="options-current">Current preferred account: <strong>' . h($_SESSION['prefaccname']) . '</strong></div>';
    echo '<div class="options-control-row"><select name="prefaccid">';
    echo '<option value="nopref">Prefer no default</option>';
    $prefacc = $con->prepare("SELECT accid, accemail FROM gwaccounts WHERE userid = ?");
    $prefacc->bind_param("i", $_SESSION['userid']);
    $prefacc->execute();
    $resacc = $prefacc->get_result();
    while ($row = $resacc->fetch_assoc()) {
       echo '<option value="' . $row['accid'] . '">' . h($row['accemail']) . '</option>';
    }
    echo '</select><button type="submit">Set account</button></div>';
    echo '<input type="hidden" name="setacc" value="update"></form>';

    echo '<form action="preferences.php" method="post" class="options-form">';
    echo '<div class="options-current">Current preferred character: <strong>' . h($_SESSION['prefcharname']) . '</strong></div>';
    echo '<div class="options-control-row"><select name="prefcharid">';
    echo '<option value="nopref">Prefer no default</option>';
    $prefchar = $con->prepare("SELECT charid, charname FROM gwchars WHERE accid = ? AND userid = ?");
    $prefchar->bind_param("ii", $_SESSION['prefaccid'], $_SESSION['userid']);
    $prefchar->execute();
    $reschar = $prefchar->get_result();
    while ($row2 = $reschar->fetch_assoc()) {
        echo '<option value="' . $row2['charid'] . '">' . h($row2['charname']) . '</option>';
    }
    echo '</select><button type="submit">Set character</button></div>';
    echo '<input type="hidden" name="setchar" value="updatechar"></form>';

    echo '<form action="preferences.php" method="post" class="options-form options-email-form">';
    echo '<label for="useremail">Update e-mail address</label>';
    echo '<div class="options-control-row"><input id="useremail" type="email" name="useremail" value="' . h($_SESSION['usermail']) . '" required><button type="submit">Update e-mail</button></div>';
    echo '</form>';
    echo '</fieldset>';

    $birthday_email_enabled = 0;
    $birthday_reminder_days = 0;
    $treasure_email_enabled = 0;
    $prefquery = $con->prepare("SELECT birthday_email_enabled, birthday_reminder_days, treasure_email_enabled FROM user_preferences WHERE userid = ?");
    $prefquery->bind_param("i", $_SESSION['userid']);
    $prefquery->execute();
    $prefresult = $prefquery->get_result();
    if ($prefrow = $prefresult->fetch_assoc()) {
        $birthday_email_enabled = (int)$prefrow['birthday_email_enabled'];
        $birthday_reminder_days = (int)$prefrow['birthday_reminder_days'];
        $treasure_email_enabled = (int)$prefrow['treasure_email_enabled'];
    }
    $prefquery->close();

    echo '<form action="preferences.php" method="post">';
    echo '<fieldset class="birthday-preferences">';
    echo '<legend>E-mail reminders</legend>';
    echo '<label class="birthday-toggle"><input type="checkbox" name="birthday_email_enabled" value="1"' . ($birthday_email_enabled === 1 ? ' checked' : '') . '><span>E-mail me reminders for my characters\' birthdays</span></label>';
    echo '<div class="birthday-reminder-row"><label for="birthday_reminder_days">Send reminder</label><select id="birthday_reminder_days" name="birthday_reminder_days">';
    foreach (array(0 => 'on the birthday', 1 => '1 day before', 3 => '3 days before', 7 => '7 days before') as $days => $label) {
        echo '<option value="' . $days . '"' . ($birthday_reminder_days === $days ? ' selected' : '') . '>' . h($label) . '</option>';
    }
    echo '</select></div>';
    echo '<label class="birthday-toggle"><input type="checkbox" name="treasure_email_enabled" value="1"' . ($treasure_email_enabled === 1 ? ' checked' : '') . '><span>E-mail me when my characters\' treasures are ready to collect again</span></label>';
    echo '<p class="birthday-help">Treasure reminders use a 31-day buffer after the most recent collection.</p>';
    echo '<input type="hidden" name="save_email_preferences" value="1">';
    echo '<button type="submit">Save e-mail preferences</button>';
    echo '<p class="birthday-help">Birthday and treasure reminders are disabled unless you explicitly opt in.</p>';
    echo '</fieldset></form>';
    if ($preference_message !== '') {
        echo '<p><strong>' . h($preference_message) . '</strong></p>';
    }

    echo <<<UPDPASS
    <fieldset class="options-card password-options-card">
    <legend>Change password</legend>
    <form action="preferences.php" method="post" class="password-options-form">
        <label for="oldpass">Current password</label>
        <input type="password" name="oldpass" id="oldpass" autocomplete="current-password" required>
        <label for="up1">New password</label>
        <input type="password" name="userpass1" id="up1" autocomplete="new-password" required>
        <label for="up2">Verify new password</label>
        <input type="password" name="userpass2" id="up2" autocomplete="new-password" required>
        <button type="submit" name="submission" id="btnSubmit">Update password</button>
    </form>
    </fieldset>
    <script type="text/javascript">
        document.getElementById("btnSubmit").addEventListener("click", function (event) {
            var userpass1 = document.getElementById("up1").value;
            var userpass2 = document.getElementById("up2").value;
            if (userpass1 !== userpass2) {
                event.preventDefault();
                alert("Passwords do not match.");
            }
        });
    </script>
UPDPASS;

    echo '<style>
    .options-page { width: min(100%, 760px); margin: 0 auto; text-align: center; }
    .options-page > h3 { margin: 10px 0 24px; }
    .options-card { width: min(100%, 560px); margin: 28px auto 10px; padding: 18px 20px 20px; border: 1px solid #2d6978; border-radius: 7px; background: #122936; text-align: left; }
    .options-card legend { padding: 0 8px; color: #69dbe1; font-size: 17px; }
    .options-card label { float: none; width: auto; margin: 0; padding: 0; text-align: left; }
    .options-form { width: 100%; margin: 0 0 22px; }
    .options-form:last-child { margin-bottom: 0; }
    .options-current, .options-email-form > label { display: block; margin-bottom: 8px; color: #dce8ee; font-weight: 600; }
    .options-current strong { color: #f3e3bd; }
    .options-control-row { display: grid; grid-template-columns: minmax(0, 1fr) auto; gap: 10px; align-items: center; }
    .options-control-row select, .options-control-row input, .password-options-form input { width: 100%; min-height: 38px; padding: 6px 10px; border: 1px solid #547080; border-radius: 5px; background: #edf2f5; color: #17242c; font: 15px "Segoe UI", Tahoma, Arial, sans-serif; }
    .options-card button { min-height: 38px; padding: 7px 14px; border: 1px solid #2999a5; border-radius: 4px; background: #174454; color: #fff; font-weight: 600; cursor: pointer; }
    .options-card button:hover { background: #1b5668; }
    .password-options-form { display: grid; grid-template-columns: 150px minmax(0, 1fr); gap: 12px 14px; align-items: center; }
    .password-options-form label { font-weight: 600; }
    .password-options-form button { grid-column: 2; justify-self: start; }
    @media (max-width: 600px) {
        .options-control-row, .password-options-form { grid-template-columns: 1fr; }
        .password-options-form button { grid-column: 1; }
        .options-control-row button, .password-options-form button { width: 100%; }
    }
    </style>';

    echo '</section>';
}
include_once ('footer.php');
?>