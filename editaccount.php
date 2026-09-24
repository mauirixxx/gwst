<?php
$pagetitle = "Edit Guild Wars account";
include_once ('header.php');
?>
<style>
.edit-account-page { width:min(100%,760px); margin:0 auto; padding:8px 0 30px; }
.edit-account-heading { margin-bottom:24px; text-align:center; }
.edit-account-heading h1 { margin:0 0 8px; }
.edit-account-heading p { margin:0; color:#a9c5d1; }
.edit-account-card { padding:24px 26px; border:1px solid #2d6978; border-radius:9px; background:#122936; }
.edit-account-field label { display:block; float:none; width:auto; margin:0 0 7px; padding:0; color:#eef5f7; font-weight:700; text-align:left; }
.edit-account-field input { width:100%; min-height:48px; box-sizing:border-box; padding:8px 12px; border:1px solid #708895; border-radius:5px; background:#edf2f5; color:#17242c; font:18px "Segoe UI",Tahoma,Arial,sans-serif; }
.edit-account-help { margin:8px 0 0; color:#9fc0cd; line-height:1.4; }
.edit-account-actions { display:flex; gap:12px; margin-top:22px; }
.edit-account-button { display:inline-flex; align-items:center; justify-content:center; min-height:46px; padding:8px 18px; border:1px solid #2999a5; border-radius:5px; background:#174454; color:#fff !important; font-weight:700; text-decoration:none !important; cursor:pointer; }
.edit-account-secondary { border-color:#526b77; background:#243943; }
.edit-account-return { margin-top:18px; text-align:center; }
.edit-account-return .edit-account-button { min-width:190px; }
.edit-account-message { margin:0 0 18px; padding:11px 14px; border-radius:6px; text-align:center; }
.edit-account-success { border:1px solid #3e7d59; background:#173526; color:#a9efc2; }
.edit-account-error { border:1px solid #8d5050; background:#3b2020; color:#ffd0d0; }
</style>
<?php
if (isset($_SESSION['userid'])) {
    $accid = $_SERVER['REQUEST_METHOD'] === 'POST' ? (int)($_POST['accid'] ?? 0) : (int)($_GET['accid'] ?? 0);
    $stmt = $con->prepare('SELECT accid, accemail FROM gwaccounts WHERE accid = ? AND userid = ? LIMIT 1');
    $stmt->bind_param('ii', $accid, $_SESSION['userid']);
    $stmt->execute();
    $account = $stmt->get_result()->fetch_assoc();
    $stmt->close();

    echo '<section class="edit-account-page">';
    if (!$account) {
        echo '<div class="edit-account-heading"><h1>Account not found</h1></div><div class="edit-account-card"><p>That Guild Wars account does not exist or does not belong to you.</p><div class="edit-account-actions"><a class="edit-account-button edit-account-secondary" href="addaccounts.php">Return to accounts &amp; characters</a></div></div></section>';
    } else {
        $message = '';
        $messageClass = '';
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $newName = trim((string)($_POST['accemail'] ?? ''));
            if ($newName === '' || mb_strlen($newName) > 50) {
                $message = 'Account e-mail or alias must be between 1 and 50 characters.';
                $messageClass = 'edit-account-error';
            } else {
                $con->begin_transaction();
                try {
                    $update = $con->prepare('UPDATE gwaccounts SET accemail = ? WHERE accid = ? AND userid = ?');
                    $update->bind_param('sii', $newName, $accid, $_SESSION['userid']);
                    $update->execute();
                    $update->close();
                    if ((int)($_SESSION['prefaccid'] ?? 0) === $accid) {
                        $pref = $con->prepare('UPDATE userinfo SET prefaccname = ? WHERE userid = ? AND prefaccid = ?');
                        $pref->bind_param('sii', $newName, $_SESSION['userid'], $accid);
                        $pref->execute();
                        $pref->close();
                    }
                    $con->commit();
                    if ((int)($_SESSION['prefaccid'] ?? 0) === $accid) {
                        $_SESSION['prefaccname'] = $newName;
                    }
                    $account['accemail'] = $newName;
                    $message = 'Guild Wars account updated.';
                    $messageClass = 'edit-account-success';
                } catch (Throwable $e) {
                    $con->rollback();
                    error_log('Guild Wars account rename failed for user ' . (int)$_SESSION['userid'] . ', account ' . $accid . ': ' . $e->getMessage());
                    $message = 'Unable to update the Guild Wars account right now.';
                    $messageClass = 'edit-account-error';
                }
            }
        }
        echo '<div class="edit-account-heading"><h1>Edit Guild Wars account</h1><p>Correct the tracked Guild Wars login e-mail or replace it with an alias.</p></div>';
        if ($message !== '') echo '<div class="edit-account-message ' . $messageClass . '">' . h($message) . '</div>';
        echo '<div class="edit-account-card"><form action="editaccount.php" method="post">' . csrf_input();
        echo '<input type="hidden" name="accid" value="' . (int)$account['accid'] . '">';
        echo '<div class="edit-account-field"><label for="accemail">Account e-mail or alias</label><input id="accemail" type="text" name="accemail" maxlength="50" value="' . h($account['accemail']) . '" required autofocus><p class="edit-account-help">This is the Guild Wars account identifier used by GWTTT. Changing it does not change your GWTTT login e-mail.</p></div>';
        echo '<div class="edit-account-actions"><button class="edit-account-button" type="submit">Save account</button><a class="edit-account-button edit-account-secondary" href="addaccounts.php">Cancel</a></div></form></div>';
        echo '<div class="edit-account-return"><a class="edit-account-button edit-account-secondary" href="addaccounts.php">Back to Accounts</a></div></section>';
    }
}
include_once ('footer.php');
?>
