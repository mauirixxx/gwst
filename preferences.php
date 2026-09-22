<?php
$pagetitle = "Account options";
include_once ('header.php');
if (isset($_SESSION['userid'])){
    $preference_message = '';

    if (isset($_POST['save_email_preferences'])) {
        $birthday_email_enabled = isset($_POST['birthday_email_enabled']) ? 1 : 0;
        $birthday_reminder_days = (int)($_POST['birthday_reminder_days'] ?? 0);
        if (!in_array($birthday_reminder_days, array(0, 1, 3, 7), true)) {
            $birthday_reminder_days = 0;
        }
        $prefstmt = $con->prepare("INSERT INTO user_preferences (userid, birthday_email_enabled, birthday_reminder_days) VALUES (?, ?, ?) ON DUPLICATE KEY UPDATE birthday_email_enabled = VALUES(birthday_email_enabled), birthday_reminder_days = VALUES(birthday_reminder_days)");
        $prefstmt->bind_param("iii", $_SESSION['userid'], $birthday_email_enabled, $birthday_reminder_days);
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
    echo '<h3>Set preferred account & character, or change e-mail or password</h3>';
    echo '<form action="preferences.php" method="post"><table border="1"><caption style="white-space: nowrap; overflow: hidden;">Current preferred account: <b>' . h($_SESSION['prefaccname']) . '</b></caption>';
    echo '<tr><td><select name="prefaccid">';
    echo '<option value="nopref">Prefer no default</option>';
    $prefacc = $con->prepare("SELECT accid, accemail FROM gwaccounts WHERE userid = ?");
    $prefacc->bind_param("i", $_SESSION['userid']);
    $prefacc->execute();
    $resacc = $prefacc->get_result();
    while ($row = $resacc->fetch_assoc()) {
       echo '<option value="' . $row['accid'] . '">' . h($row['accemail']) . '</option>';
    }
    echo '</td><td><input type="submit" value="Set account"></td></tr></select></table><input type="hidden" name="setacc" value="update"></form><br />';
    echo '<form action="preferences.php" method="post"><table border="1"><caption style="white-space: nowrap; overflow: hidden;">Current preferred character: <b>' . h($_SESSION['prefcharname']) . '</b></caption>';
    echo '<tr><td><select name="prefcharid">';
    echo '<option value="nopref">Prefer no default</option>';
    $prefchar = $con->prepare("SELECT charid, charname FROM gwchars WHERE accid = ? AND userid = ?");
    $prefchar->bind_param("ii", $_SESSION['prefaccid'], $_SESSION['userid']);
    $prefchar->execute();
    $reschar = $prefchar->get_result();
    while ($row2 = $reschar->fetch_assoc()) {
        echo '<option value="' . $row2['charid'] . '">' . h($row2['charname']) . '</option>';
    }
    echo '</td><td><input type="submit" value="Set character"></td></tr></select></table><input type="hidden" name="setchar" value="updatechar"></form><br />';
    echo '<form action="preferences.php" method="post"><table border="1">';
    echo '<caption>Update e-mail address</caption>';
    echo '<tr><td><input type="text" name="useremail" value="' . h($_SESSION['usermail']) . '"></td><td><input type="submit" value="Update e-mail"></td></tr>';
    echo '</table></form><br />';

    $birthday_email_enabled = 0;
    $birthday_reminder_days = 0;
    $prefquery = $con->prepare("SELECT birthday_email_enabled, birthday_reminder_days FROM user_preferences WHERE userid = ?");
    $prefquery->bind_param("i", $_SESSION['userid']);
    $prefquery->execute();
    $prefresult = $prefquery->get_result();
    if ($prefrow = $prefresult->fetch_assoc()) {
        $birthday_email_enabled = (int)$prefrow['birthday_email_enabled'];
        $birthday_reminder_days = (int)$prefrow['birthday_reminder_days'];
    }
    $prefquery->close();

    echo '<form action="preferences.php" method="post" class="email-preferences-form">';
    echo '<fieldset class="email-preferences-card">';
    echo '<legend>Birthday e-mail reminders</legend>';
    echo '<label class="email-pref-toggle"><input type="checkbox" name="birthday_email_enabled" value="1"' . ($birthday_email_enabled === 1 ? ' checked' : '') . '><span>E-mail me reminders for my characters\' birthdays</span></label>';
    echo '<div class="email-pref-row"><label for="birthday_reminder_days">Send reminder</label><select id="birthday_reminder_days" name="birthday_reminder_days">';
    foreach (array(0 => 'on the birthday', 1 => '1 day before', 3 => '3 days before', 7 => '7 days before') as $days => $label) {
        echo '<option value="' . $days . '"' . ($birthday_reminder_days === $days ? ' selected' : '') . '>' . h($label) . '</option>';
    }
    echo '</select></div>';
    echo '<input type="hidden" name="save_email_preferences" value="1">';
    echo '<button type="submit">Save e-mail preferences</button>';
    echo '<p class="email-pref-note">Birthday reminders are disabled unless you explicitly opt in.</p>';
    echo '</fieldset></form>';
    if ($preference_message !== '') {
        echo '<p><strong>' . h($preference_message) . '</strong></p>';
    }
    echo '<br />';

    echo <<<UPDPASS
    <form action="preferences.php" method="post"><table border="1">
    <tr><th>Old Password</th><tr>
    <tr><td><input type="password" name="oldpass" required></td></tr>
    <tr><th>New password</th></tr>
    <tr><td><input type="password" required="required" name="userpass1" id="up1"></td></tr>
    <tr><th>Verify password</th></tr>
    <tr><td><input type="password" required="required" name="userpass2" id="up2"></td></tr>
    </table><script type="text/javascript">
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
    <input type="submit" name="submission" value="Update password" onclick="return Validate()" id="btnSubmit"></form>
UPDPASS;
}
include_once ('footer.php');
?>