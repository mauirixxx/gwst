<?php
$pagetitle = "Edit Guild Wars character";
include_once ('header.php');

if (isset($_SESSION['userid'])) {
    $charid = 0;
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        $charid = (int)($_POST['charid'] ?? 0);
    } else {
        $charid = (int)($_GET['charid'] ?? 0);
    }

    $char_stmt = $con->prepare(
        "SELECT c.charid, c.accid, c.charname, c.birthdate, c.profcolor, p.profession
         FROM gwchars c
         LEFT JOIN gwprofessions p ON p.profid = c.profid
         WHERE c.charid = ? AND c.userid = ?
         LIMIT 1"
    );
    $char_stmt->bind_param("ii", $charid, $_SESSION['userid']);
    $char_stmt->execute();
    $character = $char_stmt->get_result()->fetch_assoc();
    $char_stmt->close();

    if (!$character) {
        http_response_code(404);
        echo '<section class="edit-character-page">';
        echo '<div class="edit-character-heading"><h1>Character not found</h1></div>';
        echo '<div class="edit-character-card">';
        echo '<p>That character does not exist or does not belong to your account.</p>';
        echo '<div class="edit-character-actions"><a href="addaccounts.php" class="edit-character-button edit-character-button-secondary">Return to Manage Accounts &amp; Characters</a></div>';
        echo '</div></section>';
    } else {
        $character_message = '';
        $message_class = '';

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $new_name = trim($_POST['charname'] ?? '');
            $birthdate = trim($_POST['birthdate'] ?? '');

            if ($new_name === '' || mb_strlen($new_name) > 19) {
                $character_message = 'Character name must be between 1 and 19 characters.';
                $message_class = 'edit-character-error';
            } elseif ($birthdate !== '' && !preg_match('/^\d{4}-\d{2}-\d{2}$/', $birthdate)) {
                $character_message = 'Please enter a valid birthdate.';
                $message_class = 'edit-character-error';
            } else {
                $birthdate_value = ($birthdate === '') ? null : $birthdate;
                $update = $con->prepare(
                    "UPDATE gwchars
                     SET charname = ?, birthdate = ?
                     WHERE charid = ? AND userid = ?"
                );
                $update->bind_param("ssii", $new_name, $birthdate_value, $charid, $_SESSION['userid']);
                $update->execute();
                $update->close();

                if ((int)($_SESSION['prefcharid'] ?? 0) === $charid) {
                    $_SESSION['prefcharname'] = $new_name;
                }

                $character['charname'] = $new_name;
                $character['birthdate'] = $birthdate_value;
                $character_message = 'Character <strong>' . h($new_name) . '</strong> has been updated!';
                $message_class = 'edit-character-success';
            }
        }

        $professionColor = trim((string)($character['profcolor'] ?? ''));
        if (!preg_match('/^#[0-9a-fA-F]{3,8}$/', $professionColor)) {
            $professionColor = '#122936';
        }

        echo '<section class="edit-character-page">';
        echo '<div class="edit-character-heading">';
        echo '<h1>Edit character</h1>';
        echo '<p>Update the character name after a Guild Wars rename, or correct the character birthdate.</p>';
        echo '</div>';

        if ($character_message !== '') {
            echo '<div class="edit-character-message ' . $message_class . '">' . $character_message . '</div>';
        }

        echo '<fieldset class="edit-character-card edit-character-profession-card" style="--profession-color:' . h($professionColor) . ';">';
        echo '<legend>Character details</legend>';
        echo '<form action="editcharacter.php" method="post" class="edit-character-form">';
        echo '<input type="hidden" name="charid" value="' . (int)$character['charid'] . '">';
        echo '<div class="edit-character-row"><label for="edit-charname">Character name</label><input id="edit-charname" type="text" name="charname" maxlength="19" value="' . h($character['charname']) . '" required></div>';
        echo '<div class="edit-character-row"><label for="edit-birthdate">Birthdate</label><input id="edit-birthdate" type="date" name="birthdate" value="' . h($character['birthdate'] ?? '') . '"></div>';
        echo '<div class="edit-character-row"><span class="edit-character-label">Profession</span><span class="edit-character-value">' . h($character['profession'] ?? 'Unknown') . '</span></div>';
        echo '<div class="edit-character-actions"><button type="submit" class="edit-character-button">Save character changes</button><a href="addaccounts.php" class="edit-character-button edit-character-button-secondary">Cancel</a></div>';
        echo '</form>';
        echo '</fieldset>';
        echo '</section>';
    }
}

include_once ('footer.php');
?>