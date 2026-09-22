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
        "SELECT c.charid, c.accid, c.charname, c.birthdate, p.profession
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
        echo '<h2>Character not found</h2>';
        echo '<p>That character does not exist or does not belong to your account.</p>';
    } else {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $new_name = trim($_POST['charname'] ?? '');
            $birthdate = trim($_POST['birthdate'] ?? '');

            if ($new_name === '' || mb_strlen($new_name) > 19) {
                echo '<p>Character name must be between 1 and 19 characters.</p>';
            } elseif ($birthdate !== '' && !preg_match('/^\d{4}-\d{2}-\d{2}$/', $birthdate)) {
                echo '<p>Please enter a valid birthdate.</p>';
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
                echo '<p>Character <strong>' . h($new_name) . '</strong> has been updated!</p>';
            }
        }

        echo '<section class="edit-character-panel options-page">';
        echo '<h2>Edit character</h2>';
        echo '<p>Update the character name after a Guild Wars rename, or correct the character birthdate.</p>';
        echo '<form action="editcharacter.php" method="post">';
        echo '<input type="hidden" name="charid" value="' . (int)$character['charid'] . '">';
        echo '<table>';
        echo '<tr><th>Character name</th><td><input type="text" name="charname" maxlength="19" size="24" value="' . h($character['charname']) . '" required></td></tr>';
        echo '<tr><th>Birthdate</th><td><input type="date" name="birthdate" value="' . h($character['birthdate'] ?? '') . '"></td></tr>';
        echo '<tr><th>Profession</th><td>' . h($character['profession'] ?? 'Unknown') . '</td></tr>';
        echo '<tr><td colspan="2"><input type="submit" value="Save character changes"></td></tr>';
        echo '</table>';
        echo '</form>';
        echo '<p><a href="addaccounts.php" class="navlink">Return to Manage Accounts &amp; Characters</a></p>';
        echo '</section>';
    }
}

include_once ('footer.php');
?>