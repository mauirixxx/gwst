<?php
$pagetitle = "Edit Guild Wars character";
include_once ('header.php');
?>
<style>
.edit-character-page { width: min(100%, 900px); margin: 0 auto; padding: 8px 0 30px; }
.edit-character-heading { margin-bottom: 26px; text-align: center; }
.edit-character-heading h1 { margin: 0 0 10px; color: #eef5f7; font-size: 34px; }
.edit-character-heading p { margin: 0; color: #a9c5d1; font-size: 18px; }
.edit-character-card-heading { margin: 0 0 12px; color: #eef5f7; font-size: 25px; font-weight: 400; text-align: center; text-shadow: 0 1px 3px rgba(0,0,0,.9); }
.edit-character-card { margin: 0 auto; padding: 26px 30px 30px; border: 1px solid #2d6978; border-radius: 9px; background: #122936; }
.edit-character-profession-card { position: relative; overflow: hidden; background: var(--profession-color, #122936); border-color: #5f7b86; box-shadow: 0 14px 34px rgba(0,0,0,.24); color: #10202a; }
.edit-character-profession-card::before { content: ""; position: absolute; inset: 0; pointer-events: none; background: linear-gradient(180deg, rgba(255,255,255,.12), rgba(255,255,255,.02)); }
.edit-character-form { position: relative; z-index: 1; width: min(100%, 620px); margin: 0 auto; padding: 4px 0; }
.edit-character-row { display: grid; grid-template-columns: 180px 1fr; gap: 16px; align-items: center; margin-bottom: 16px; }
.edit-character-row label, .edit-character-label { float: none; width: auto; margin: 0; padding: 0; color: #10202a; font-size: 17px; font-weight: 700; text-align: right; text-shadow: 0 1px rgba(255,255,255,.35); }
.edit-character-row input { width: 100%; min-height: 48px; padding: 8px 12px; border: 1px solid rgba(16,32,42,.55); border-radius: 5px; background: rgba(247,250,252,.94); color: #17242c; font: 18px "Segoe UI", Tahoma, Arial, sans-serif; box-shadow: 0 1px 2px rgba(0,0,0,.12); }
.edit-character-value { min-height: 48px; padding: 12px; border: 1px solid rgba(16,32,42,.38); border-radius: 5px; background: rgba(255,255,255,.28); color: #10202a; font-size: 18px; font-weight: 700; text-align: left; }
.edit-character-actions { display: flex; justify-content: center; gap: 12px; margin-top: 24px; }
.edit-character-button { display: inline-flex; align-items: center; justify-content: center; min-height: 48px; padding: 9px 20px; border: 1px solid #2999a5; border-radius: 5px; background: #174454; color: #fff !important; font: 600 17px "Segoe UI", Tahoma, Arial, sans-serif; text-decoration: none !important; cursor: pointer; }
.edit-character-button:hover { background: #1b5668; }
.edit-character-button-secondary { border-color: #526b77; background: #243943; }
.edit-character-message { width: min(100%, 620px); margin: 0 auto 20px; padding: 11px 14px; border-radius: 6px; text-align: center; }
.edit-character-success { border: 1px solid #3e7d59; background: #173526; color: #a9efc2; }
.edit-character-error { border: 1px solid #8d5050; background: #3b2020; color: #ffd0d0; }
@media (max-width: 650px) { .edit-character-card { padding: 20px 16px 22px; } .edit-character-row { grid-template-columns: 1fr; gap: 6px; } .edit-character-row label, .edit-character-label { text-align: left; } .edit-character-actions { flex-direction: column; } .edit-character-button { width: 100%; } }
</style>
<?php
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
            $new_name = trim((string)($_POST['charname'] ?? ''));
            $birthdate = trim((string)($_POST['birthdate'] ?? ''));

            if ($new_name === '' || mb_strlen($new_name) > 19) {
                $character_message = 'Character name must be between 1 and 19 characters.';
                $message_class = 'edit-character-error';
            } else {
                $birthdate_value = null;
                if ($birthdate !== '') {
                    $parsed_date = DateTimeImmutable::createFromFormat('!Y-m-d', $birthdate);
                    $date_errors = DateTimeImmutable::getLastErrors();
                    if (!$parsed_date || ($date_errors !== false && ($date_errors['warning_count'] > 0 || $date_errors['error_count'] > 0)) || $parsed_date->format('Y-m-d') !== $birthdate) {
                        $character_message = 'Please enter a valid birthdate.';
                        $message_class = 'edit-character-error';
                    } else {
                        $birthdate_value = $birthdate;
                    }
                }

                if ($message_class === '') {
                    $update = $con->prepare(
                        "UPDATE gwchars SET charname = ?, birthdate = ? WHERE charid = ? AND userid = ?"
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

        echo '<h2 class="edit-character-card-heading">Character details</h2>';
        echo '<div class="edit-character-card edit-character-profession-card" style="--profession-color:' . h($professionColor) . ';">';
        echo '<form action="editcharacter.php" method="post" class="edit-character-form">';
        echo csrf_input();
        echo '<input type="hidden" name="charid" value="' . (int)$character['charid'] . '">';
        echo '<div class="edit-character-row"><label for="edit-charname">Character name</label><input id="edit-charname" type="text" name="charname" maxlength="19" value="' . h($character['charname']) . '" required></div>';
        echo '<div class="edit-character-row"><label for="edit-birthdate">Birthdate</label><input id="edit-birthdate" type="date" name="birthdate" value="' . h($character['birthdate'] ?? '') . '"></div>';
        echo '<div class="edit-character-row"><span class="edit-character-label">Profession</span><span class="edit-character-value">' . h($character['profession'] ?? 'Unknown') . '</span></div>';
        echo '<div class="edit-character-actions"><button type="submit" class="edit-character-button">Save character changes</button><a href="addaccounts.php" class="edit-character-button edit-character-button-secondary">Cancel</a></div>';
        echo '</form>';
        echo '</div>';
        echo '</section>';
    }
}

include_once ('footer.php');
?>