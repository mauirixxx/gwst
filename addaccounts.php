<?php
$pagetitle = "Add a Guild Wars account to track";
include_once ('header.php');
if (isset($_SESSION['userid'])) {
    if (!empty($_POST['prefcharid'])) include_once ('includes/set-prefchar.php');
    if (!empty($_POST['prefaccid'])) include_once ('includes/set-prefacc.php');
    if (!empty($_POST['accemail'])) include_once ('includes/addaccount-submit.php');
    if (!empty($_POST['delaccid'])) include_once ('includes/del-account.php');
    if (!empty($_POST['delcharid'])) include_once ('includes/del-character.php');
    if (!empty($_POST['newcharname'])) include_once ('includes/addcharacters-submit.php');

    $account_count_stmt = $con->prepare("SELECT COUNT(*) FROM gwaccounts WHERE userid = ?");
    $account_count_stmt->bind_param("i", $_SESSION['userid']);
    $account_count_stmt->execute();
    $account_count_stmt->bind_result($existing_account_count);
    $account_count_stmt->fetch();
    $account_count_stmt->close();
    $suggested_account_name = ((int)$existing_account_count === 0) ? $_SESSION['usermail'] : '';
?>
<style>
.manage-page { width: min(100%, 1120px); margin: 0 auto; }
.manage-page > h1 { margin: 8px 0 8px; text-align: center; }
.manage-intro { margin: 0 0 28px; color: #a9c5d1; text-align: center; }
.manage-panel { margin: 0 0 30px; padding: 24px 26px 26px; border: 1px solid #2d6978; border-radius: 9px; background: #122936; }
.manage-panel legend { padding: 0 12px; color: #69dbe1; font-size: 25px; }
.manage-subheading { margin: 2px 0 16px; color: #eef5f7; font-size: 20px; }
.manage-help { margin: -6px 0 15px; color: #9fc0cd; }
.manage-add-row { display: flex; gap: 12px; align-items: end; margin-bottom: 28px; }
.manage-field { flex: 1 1 auto; }
.manage-field label { display: block; float: none; width: auto; margin: 0 0 7px; padding: 0; color: #eef5f7; text-align: left; }
.manage-page input[type="text"], .manage-page input[type="date"], .manage-page select { width: 100%; min-height: 48px; padding: 8px 12px; border: 1px solid #708895; border-radius: 5px; background: #edf2f5; color: #17242c; font: 18px "Segoe UI", Tahoma, Arial, sans-serif; }
.manage-page input[type="radio"], .manage-page input[type="checkbox"] { width: 21px; height: 21px; accent-color: #28b8c0; }
.manage-button { min-height: 48px; padding: 9px 18px; border: 1px solid #2999a5; border-radius: 5px; background: #174454; color: #fff; font: 600 17px "Segoe UI", Tahoma, Arial, sans-serif; cursor: pointer; }
.manage-button:hover { background: #1b5668; }
.manage-table-wrap { overflow-x: auto; border: 1px solid #315463; border-radius: 7px; }
.manage-table { width: 100%; border-collapse: collapse; background: #10242e; }
.manage-table th { padding: 12px 14px; background: #1a3a4b; border-right: 1px solid #355765; border-bottom: 2px solid #28b8c0; color: #f2f6f7; font-size: 14px; text-transform: uppercase; }
.manage-table td { padding: 11px 14px; border-right: 1px solid #294754; border-bottom: 1px solid #294754; color: #e1edf1; font-size: 16px; }
.manage-table th:last-child, .manage-table td:last-child { border-right: 0; }
.manage-table tr:last-child td { border-bottom: 0; }
.manage-table tbody tr:hover td { background: rgba(40,184,192,.07); }
.manage-id { width: 80px; text-align: center; }
.manage-choice { width: 115px; text-align: center; }
.manage-account-link { color: #fff27a !important; font-weight: 600; }
.manage-char-link { color: #10202a !important; font-weight: 600; }
.manage-actions { margin-top: 16px; text-align: right; }
.manage-character-grid { display: grid; grid-template-columns: minmax(220px, 1.5fr) minmax(180px, 1fr) minmax(180px, 1fr) auto; gap: 12px; align-items: end; margin-bottom: 28px; }
.manage-empty { padding: 18px; border: 1px dashed #426675; border-radius: 6px; color: #b9ced7; text-align: center; }
.manage-return { margin: 6px 0 24px; text-align: center; }
@media (max-width: 760px) { .manage-panel { padding: 18px 14px 20px; } .manage-add-row { display: grid; } .manage-character-grid { grid-template-columns: 1fr; } .manage-actions { text-align: center; } .manage-button { width: 100%; } }
</style>
<section class="manage-page">
<h1>Manage accounts &amp; characters</h1>
<p class="manage-intro">Add, select, and maintain the Guild Wars accounts and characters tracked by GWTTT.</p>
<fieldset class="manage-panel"><legend>Guild Wars accounts</legend>
<h2 class="manage-subheading">Add a new account e-mail or alias</h2>
<?php if ((int)$existing_account_count === 0): ?><p class="manage-help">Your signup e-mail is suggested below. Keep it, replace it with your Guild Wars login e-mail, or use an alias.</p><?php endif; ?>
<form action="addaccounts.php" method="post" class="manage-add-row"><?php echo csrf_input(); ?>
<div class="manage-field"><label for="accemail">Account e-mail or alias</label><input id="accemail" type="text" name="accemail" maxlength="50" value="<?php echo h($suggested_account_name); ?>" required></div>
<input class="manage-button" type="submit" value="Add account"></form>
<h2 class="manage-subheading">Current Guild Wars accounts</h2>
<form action="addaccounts.php" method="post"><?php echo csrf_input(); ?>
<div class="manage-table-wrap"><table class="manage-table"><thead><tr><th class="manage-id">ID</th><th>Account name</th><th class="manage-choice">Preferred</th><th class="manage-choice">Delete?</th></tr></thead><tbody>
<?php
    $acclist = $con->prepare("SELECT accid, accemail FROM gwaccounts WHERE userid = ?");
    $acclist->bind_param("i", $_SESSION['userid']); $acclist->execute(); $result = $acclist->get_result();
    while ($row = $result->fetch_assoc()) {
        echo '<tr><td class="manage-id">' . (int)$row['accid'] . '</td><td><span class="manage-account-link">' . h($row['accemail']) . '</span></td>';
        echo '<td class="manage-choice"><input type="radio" name="prefaccid" value="' . (int)$row['accid'] . '" aria-label="Make ' . h($row['accemail']) . ' preferred"' . ($row['accid'] == $_SESSION['prefaccid'] ? ' checked' : '') . '></td>';
        echo '<td class="manage-choice"><input type="checkbox" name="delaccid[]" value="' . (int)$row['accid'] . '" aria-label="Delete ' . h($row['accemail']) . '"></td></tr>';
    }
    $acclist->close();
?>
</tbody></table></div><div class="manage-actions"><input class="manage-button" type="submit" value="Modify selected accounts"></div></form></fieldset>
<?php
    $selected_account_id = (int)($_SESSION['prefaccid'] ?? 0); $selected_account = null;
    if ($selected_account_id > 0) {
        $ownacc = $con->prepare("SELECT accid, accemail FROM gwaccounts WHERE accid = ? AND userid = ? LIMIT 1");
        $ownacc->bind_param("ii", $selected_account_id, $_SESSION['userid']); $ownacc->execute();
        $selected_account = $ownacc->get_result()->fetch_assoc(); $ownacc->close();
    }
?>
<fieldset class="manage-panel"><legend>Characters</legend>
<?php if (!$selected_account): ?><div class="manage-empty">Add and select a Guild Wars account before adding characters.</div>
<?php else: ?>
<h2 class="manage-subheading">Add character to <?php echo h($selected_account['accemail']); ?></h2>
<form action="addaccounts.php" method="post" class="manage-character-grid"><?php echo csrf_input(); ?>
<div class="manage-field"><label for="newcharname">Character name</label><input id="newcharname" type="text" name="newcharname" maxlength="19" required></div>
<div class="manage-field"><label for="bdate">Birthdate</label><input id="bdate" type="date" name="bdate"></div>
<div class="manage-field"><label for="profid">Profession</label><select id="profid" name="profid" required>
<?php
        $gp = $con->prepare("SELECT profid, profession FROM gwprofessions"); $gp->execute(); $result = $gp->get_result();
        while ($row = $result->fetch_assoc()) echo '<option value="' . (int)$row['profid'] . '">' . h($row['profession']) . '</option>';
        $gp->close();
?>
</select></div><input class="manage-button" type="submit" value="Add character"></form><?php endif; ?>
<h2 class="manage-subheading">Available characters</h2>
<form action="addaccounts.php" method="post"><?php echo csrf_input(); ?>
<div class="manage-table-wrap"><table class="manage-table"><thead><tr><th class="manage-id">ID</th><th class="manage-id">Account</th><th>Character name</th><th>Birthdate</th><th class="manage-choice">Preferred</th><th class="manage-choice">Delete?</th></tr></thead><tbody>
<?php
    $lc = $con->prepare("SELECT charid, accid, charname, birthdate, profid, profcolor FROM gwchars WHERE accid = ? AND userid = ?");
    $lc->bind_param("ii", $_SESSION['prefaccid'], $_SESSION['userid']); $lc->execute(); $res2 = $lc->get_result();
    while ($row2 = $res2->fetch_assoc()) {
        echo '<tr><td class="manage-id">' . (int)$row2['charid'] . '</td><td class="manage-id">' . (int)$row2['accid'] . '</td>';
        echo '<td style="background-color:' . h($row2['profcolor']) . '"><a class="manage-char-link" href="editcharacter.php?charid=' . (int)$row2['charid'] . '">' . h($row2['charname']) . '</a></td>';
        echo '<td style="background-color:' . h($row2['profcolor']) . '; color:#10202a;">' . h($row2['birthdate'] ?: 'Not set') . '</td>';
        echo '<td class="manage-choice"><input type="radio" name="prefcharid" value="' . (int)$row2['charid'] . '" aria-label="Make ' . h($row2['charname']) . ' preferred"' . ($row2['charid'] == $_SESSION['prefcharid'] ? ' checked' : '') . '></td>';
        echo '<td class="manage-choice"><input type="checkbox" name="delcharid[]" value="' . (int)$row2['charid'] . '" aria-label="Delete ' . h($row2['charname']) . '"></td></tr>';
    }
    $lc->close();
?>
</tbody></table></div><div class="manage-actions"><input class="manage-button" type="submit" value="Modify selected characters"></div></form></fieldset>
<p class="manage-return">Return to your <a href="index.php" class="navlink">user page</a></p></section>
<?php
}
include_once ('footer.php');
?>