<?php
$pagetitle = 'User Editor';
include_once('header.php');

if (!isset($_SESSION['userid'], $_SESSION['admin']) || (int)$_SESSION['admin'] !== 1) {
    http_response_code(403);
    echo '<div class="admin-user-page"><div class="admin-user-card"><h1>Access denied</h1></div></div>';
    include_once('footer.php');
    exit();
}

$message = '';
$error = '';
$selectedUserId = filter_var($_POST['userid'] ?? $_GET['userid'] ?? null, FILTER_VALIDATE_INT);

function admin_user_load(mysqli $con, int $userId): ?array
{
    $stmt = $con->prepare('SELECT userid, username, usermail, admin, prefaccid, prefcharid FROM userinfo WHERE userid = ? LIMIT 1');
    $stmt->bind_param('i', $userId);
    $stmt->execute();
    $row = $stmt->get_result()->fetch_assoc();
    $stmt->close();
    return $row ?: null;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = (string)($_POST['action'] ?? '');
    if ($selectedUserId === false || $selectedUserId < 1) {
        $error = 'Select a valid user first.';
        $selectedUserId = null;
    } elseif ($action === 'update_email') {
        $target = admin_user_load($con, $selectedUserId);
        $email = trim((string)($_POST['usermail'] ?? ''));
        if (!$target) {
            $error = 'User not found.';
        } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL) || strlen($email) > 255) {
            $error = 'Enter a valid e-mail address.';
        } else {
            $dupe = $con->prepare('SELECT userid FROM userinfo WHERE usermail = ? AND userid <> ? LIMIT 1');
            $dupe->bind_param('si', $email, $selectedUserId);
            $dupe->execute();
            $duplicate = $dupe->get_result()->fetch_assoc();
            $dupe->close();
            if ($duplicate) {
                $error = 'That e-mail address is already assigned to another user.';
            } else {
                $stmt = $con->prepare('UPDATE userinfo SET usermail = ? WHERE userid = ?');
                $stmt->bind_param('si', $email, $selectedUserId);
                $stmt->execute();
                $stmt->close();
                if ($selectedUserId === (int)$_SESSION['userid']) {
                    $_SESSION['usermail'] = $email;
                }
                $message = 'Login e-mail updated for ' . $target['username'] . '.';
            }
        }
    } elseif ($action === 'update_password') {
        $target = admin_user_load($con, $selectedUserId);
        $password = (string)($_POST['new_password'] ?? '');
        $confirm = (string)($_POST['confirm_password'] ?? '');
        if (!$target) {
            $error = 'User not found.';
        } elseif (strlen($password) < 8 || strlen($password) > 255) {
            $error = 'Password must be between 8 and 255 characters.';
        } elseif (!hash_equals($password, $confirm)) {
            $error = 'Password confirmation does not match.';
        } else {
            $hash = password_hash($password, PASSWORD_DEFAULT);
            if ($hash === false) {
                $error = 'Unable to hash the new password.';
            } else {
                $con->begin_transaction();
                try {
                    $stmt = $con->prepare('UPDATE userinfo SET userpass = ? WHERE userid = ?');
                    $stmt->bind_param('si', $hash, $selectedUserId);
                    $stmt->execute();
                    $stmt->close();
                    $tokens = $con->prepare('DELETE FROM password_reset_tokens WHERE userid = ?');
                    $tokens->bind_param('i', $selectedUserId);
                    $tokens->execute();
                    $tokens->close();
                    $con->commit();
                    $message = 'Password reset for ' . $target['username'] . '.';
                } catch (Throwable $e) {
                    $con->rollback();
                    error_log('GWTTT admin password reset failed: ' . $e->getMessage());
                    $error = 'Unable to update that password right now.';
                }
            }
        }
    } elseif ($action === 'delete_user') {
        $target = admin_user_load($con, $selectedUserId);
        $confirmUsername = trim((string)($_POST['confirm_username'] ?? ''));
        if (!$target) {
            $error = 'User not found.';
        } elseif ($selectedUserId === (int)$_SESSION['userid']) {
            $error = 'You cannot delete the account you are currently signed in with.';
        } elseif (!hash_equals((string)$target['username'], $confirmUsername)) {
            $error = 'Deletion confirmation failed. Type the username exactly as shown.';
        } else {
            $con->begin_transaction();
            try {
                // Delete every row in this database tied to userid, leaving userinfo until last.
                // This automatically includes account/character stats, treasures, reset tokens,
                // and future userid-linked tables without maintaining a fragile hard-coded list.
                $schemaStmt = $con->prepare(
                    "SELECT DISTINCT TABLE_NAME FROM information_schema.COLUMNS
                     WHERE TABLE_SCHEMA = DATABASE() AND COLUMN_NAME = 'userid' AND TABLE_NAME <> 'userinfo'"
                );
                $schemaStmt->execute();
                $tables = $schemaStmt->get_result()->fetch_all(MYSQLI_ASSOC);
                $schemaStmt->close();
                foreach ($tables as $tableRow) {
                    $table = (string)$tableRow['TABLE_NAME'];
                    if (!preg_match('/^[A-Za-z0-9_]+$/', $table)) {
                        throw new RuntimeException('Unexpected table name while deleting user data.');
                    }
                    $delete = $con->prepare('DELETE FROM `' . $table . '` WHERE userid = ?');
                    $delete->bind_param('i', $selectedUserId);
                    $delete->execute();
                    $delete->close();
                }
                $deleteUser = $con->prepare('DELETE FROM userinfo WHERE userid = ?');
                $deleteUser->bind_param('i', $selectedUserId);
                $deleteUser->execute();
                if ($deleteUser->affected_rows !== 1) {
                    throw new RuntimeException('User row was not deleted.');
                }
                $deleteUser->close();
                $con->commit();
                $message = 'User ' . $target['username'] . ' and all userid-linked data were permanently deleted.';
                $selectedUserId = null;
            } catch (Throwable $e) {
                $con->rollback();
                error_log('GWTTT admin user deletion failed: ' . $e->getMessage());
                $error = 'Unable to delete that user. No deletion was committed.';
            }
        }
    }
}

$selectedUser = ($selectedUserId && $selectedUserId > 0) ? admin_user_load($con, (int)$selectedUserId) : null;
$users = $con->query('SELECT userid, username, usermail, admin FROM userinfo ORDER BY username');
?>
<style>
.admin-user-page{width:min(100%,1000px);margin:0 auto;padding:8px 0 34px}.admin-user-heading{text-align:center;margin-bottom:22px}.admin-user-heading h1{margin:0 0 6px}.admin-user-heading p{margin:0;color:#9fb9c4}.admin-user-card{margin-bottom:20px;padding:20px 22px;border:1px solid #2d6978;border-radius:8px;background:#122936}.admin-user-card h2{margin:0 0 8px;color:#69dbe1}.admin-user-card p{color:#b9cbd3}.admin-user-select{display:flex;gap:12px;align-items:end}.admin-user-field{flex:1}.admin-user-field label{display:block;margin-bottom:6px;color:#9fd8e5;font-weight:700}.admin-user-field input,.admin-user-field select{width:100%;min-height:42px;box-sizing:border-box;padding:7px 10px;border:1px solid #547080;border-radius:5px;background:#edf2f5;color:#17242c;font:15px "Segoe UI",Tahoma,Arial,sans-serif}.admin-user-actions{display:flex;gap:10px;margin-top:14px;align-items:center}.admin-user-button{min-height:42px;padding:8px 18px;border:1px solid #2999a5;border-radius:5px;background:#174454;color:#fff;font-weight:700;cursor:pointer}.admin-user-danger{border-color:#a74b4b;background:#5a2424}.admin-user-meta{display:grid;grid-template-columns:150px 1fr;gap:7px 12px;margin:16px 0}.admin-user-meta dt{color:#9fd8e5;font-weight:700}.admin-user-meta dd{margin:0}.admin-user-grid{display:grid;grid-template-columns:1fr 1fr;gap:20px}.admin-user-status{margin-bottom:18px;padding:12px 14px;border-radius:6px;background:#153b3e;border:1px solid #2b7d82}.admin-user-error{background:#4b2424;border-color:#a74b4b}.admin-user-danger-zone{border-color:#8f4545}.admin-user-danger-zone h2{color:#ff9d9d}.admin-user-confirm{padding:12px 14px;border:1px solid #704141;border-radius:6px;background:#28191b}.admin-user-back{color:#fff27a;font-weight:700;text-decoration:none}@media(max-width:720px){.admin-user-grid{grid-template-columns:1fr}.admin-user-select{flex-direction:column;align-items:stretch}.admin-user-meta{grid-template-columns:1fr}}
</style>
<section class="admin-user-page">
<div class="admin-user-heading"><h1>User editor</h1><p>Manage GWTTT login e-mail addresses and passwords, or permanently remove a user and their associated data.</p></div>
<?php if ($message !== ''): ?><div class="admin-user-status"><?= h($message) ?></div><?php endif; ?>
<?php if ($error !== ''): ?><div class="admin-user-status admin-user-error" role="alert"><?= h($error) ?></div><?php endif; ?>
<div class="admin-user-card"><h2>Select user</h2><form method="get" action="usermanager.php" class="admin-user-select"><div class="admin-user-field"><label for="userid">GWTTT user</label><select id="userid" name="userid" required><option value="">Choose a user…</option><?php while ($row = $users->fetch_assoc()): ?><option value="<?= (int)$row['userid'] ?>"<?= $selectedUser && (int)$selectedUser['userid']===(int)$row['userid']?' selected':'' ?>><?= h($row['username']) ?> — <?= h($row['usermail']) ?><?= (int)$row['admin']===1?' (admin)':'' ?></option><?php endwhile; ?></select></div><button class="admin-user-button" type="submit">Edit user</button></form></div>
<?php if ($selectedUser): ?>
<div class="admin-user-card"><h2><?= h($selectedUser['username']) ?></h2><dl class="admin-user-meta"><dt>User ID</dt><dd><?= (int)$selectedUser['userid'] ?></dd><dt>Username</dt><dd><?= h($selectedUser['username']) ?></dd><dt>Current e-mail</dt><dd><?= h($selectedUser['usermail']) ?></dd><dt>Administrator</dt><dd><?= (int)$selectedUser['admin']===1?'Yes':'No' ?></dd></dl></div>
<div class="admin-user-grid">
<div class="admin-user-card"><h2>Change login e-mail</h2><p>Changes the e-mail address stored on this GWTTT user account.</p><form method="post" action="usermanager.php"><?= csrf_input() ?><input type="hidden" name="userid" value="<?= (int)$selectedUser['userid'] ?>"><input type="hidden" name="action" value="update_email"><div class="admin-user-field"><label for="usermail">E-mail address</label><input id="usermail" type="email" name="usermail" maxlength="255" value="<?= h($selectedUser['usermail']) ?>" required></div><div class="admin-user-actions"><button class="admin-user-button" type="submit">Update e-mail</button></div></form></div>
<div class="admin-user-card"><h2>Set new password</h2><p>Sets a new password without requiring the user's current password. Existing password-reset links are invalidated.</p><form method="post" action="usermanager.php"><?= csrf_input() ?><input type="hidden" name="userid" value="<?= (int)$selectedUser['userid'] ?>"><input type="hidden" name="action" value="update_password"><div class="admin-user-field"><label for="new-password">New password</label><input id="new-password" type="password" name="new_password" minlength="8" maxlength="255" autocomplete="new-password" required></div><div class="admin-user-field" style="margin-top:12px"><label for="confirm-password">Confirm new password</label><input id="confirm-password" type="password" name="confirm_password" minlength="8" maxlength="255" autocomplete="new-password" required></div><div class="admin-user-actions"><button class="admin-user-button" type="submit">Set password</button></div></form></div>
</div>
<div class="admin-user-card admin-user-danger-zone"><h2>Delete user</h2><?php if ((int)$selectedUser['userid']===(int)$_SESSION['userid']): ?><p>Your currently signed-in account cannot be deleted from the User Editor.</p><?php else: ?><div class="admin-user-confirm"><p><strong>This is permanent.</strong> GWTTT will delete the user plus every row in this database associated through its <code>userid</code>, including their Guild Wars accounts, characters, title stats, treasures, and password-reset tokens.</p><p>To verify the deletion, type <strong><?= h($selectedUser['username']) ?></strong> exactly:</p><form method="post" action="usermanager.php"><?= csrf_input() ?><input type="hidden" name="userid" value="<?= (int)$selectedUser['userid'] ?>"><input type="hidden" name="action" value="delete_user"><div class="admin-user-field"><label for="confirm-username">Confirm username</label><input id="confirm-username" type="text" name="confirm_username" autocomplete="off" required></div><div class="admin-user-actions"><button class="admin-user-button admin-user-danger" type="submit">Permanently delete user</button></div></form></div><?php endif; ?></div>
<?php endif; ?>
<a class="admin-user-back" href="adminlanding.php">← Return to Administration</a>
</section>
<?php include_once('footer.php'); ?>