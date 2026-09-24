<?php
if (isset($_SESSION['userid']) && isset($_SESSION['admin']) && (int)$_SESSION['admin'] === 1) {
    $tid_request = filter_input(INPUT_POST, 'tid', FILTER_VALIDATE_INT);
    if (!$tid_request || $tid_request < 1) {
        http_response_code(400);
        echo 'Invalid title selected.';
        return;
    }

    $stmtview = $con->prepare("SELECT * FROM gwtitles WHERE titlenameid = ?");
    $stmtview->bind_param("i", $tid_request);
    $stmtview->execute();
    $result = $stmtview->get_result();
    $row = $result->fetch_assoc();
    $stmtview->close();

    if (!$row) {
        http_response_code(404);
        echo 'Title not found.';
        return;
    }

    $tid = (int)$row['titlenameid'];
    $tname = $row['titlename'];
    $ttype = (int)$row['titletype'];
    $tmr = (int)$row['titlemaxrank'];
    $taf = (int)$row['autofilled'];
    $tg = (int)$row['gwamm'];

    $ggt = $con->prepare("SELECT titlename FROM gwtitles WHERE gwamm = 1 LIMIT 1");
    $ggt->execute();
    $ggt->bind_result($gwamm);
    $ggt->fetch();
    $ggt->close();
?>
<style>
.title-edit-page { width: min(100%, 760px); margin: 0 auto; padding: 8px 0 32px; text-align: left; }
.title-edit-heading { margin: 4px 0 22px; text-align: center; }
.title-edit-heading h1 { margin: 0 0 7px; }
.title-edit-heading p { margin: 0; color: #9fb9c4; }
.title-edit-card { padding: 22px; border: 1px solid #2d6978; border-radius: 8px; background: #122936; }
.title-edit-card h2 { margin: 0 0 18px; color: #69dbe1; font-size: 20px; }
.title-edit-field { margin-bottom: 18px; }
.title-edit-field > label, .title-edit-label { display: block; margin-bottom: 7px; color: #e7f1f4; font-weight: 700; }
.title-edit-field input[type="text"], .title-edit-field input[type="number"] { box-sizing: border-box; width: 100%; min-height: 40px; padding: 8px 10px; border: 1px solid #547080; border-radius: 5px; background: #edf2f5; color: #17242c; font: 15px "Segoe UI", Tahoma, Arial, sans-serif; }
.title-edit-help { margin: 6px 0 0; color: #9fb9c4; font-size: 13px; }
.title-edit-options { display: flex; flex-wrap: wrap; gap: 10px; }
.title-edit-option { display: flex; align-items: center; gap: 8px; min-width: 150px; padding: 10px 12px; border: 1px solid #45606c; border-radius: 6px; background: #182e39; color: #e1edf1; cursor: pointer; }
.title-edit-option input { margin: 0; }
.title-edit-status { margin: 4px 0 20px; padding: 12px 14px; border: 1px solid #294b5b; border-radius: 6px; background: #101f28; color: #cfe0e7; }
.title-edit-actions { display: flex; flex-wrap: wrap; gap: 10px; align-items: center; }
.title-edit-button { min-height: 40px; padding: 8px 16px; border: 1px solid #2999a5; border-radius: 5px; background: #174454; color: #fff; font: 600 15px "Segoe UI", Tahoma, Arial, sans-serif; cursor: pointer; }
.title-edit-button:hover { background: #1b5668; }
.title-edit-back { color: #69dbe1; font-weight: 600; }
.title-edit-danger { margin-top: 24px; padding: 18px; border: 1px solid #8a4545; border-radius: 7px; background: #321d1d; }
.title-edit-danger h3 { margin: 0 0 6px; color: #ffb3b3; }
.title-edit-danger p { margin: 0 0 14px; color: #e7c4c4; font-size: 13px; }
.title-edit-delete-option { display: inline-flex !important; align-items: center; gap: 10px; margin: 0 !important; padding: 10px 12px; border: 1px solid #8a4545; border-radius: 6px; background: #261717; color: #fff !important; font-weight: 600; line-height: 1.2; cursor: pointer; }
.title-edit-delete-option input[type="checkbox"] { flex: 0 0 auto; width: 18px !important; height: 18px !important; margin: 0 !important; padding: 0 !important; }
.title-edit-delete-option span { display: inline; margin: 0; padding: 0; }
@media (max-width: 600px) { .title-edit-card { padding: 16px; } .title-edit-option { width: 100%; } .title-edit-delete-option { display: flex !important; width: auto; } }
</style>
<section class="title-edit-page">
    <div class="title-edit-heading">
        <h1>Edit title</h1>
        <p>Update the title settings without touching its rank definitions.</p>
    </div>
    <form action="titlemanager.php" method="post">
        <?php echo csrf_input(); ?>
        <input type="hidden" name="title" value="updatetitle">
        <input type="hidden" name="titlenameid" value="<?php echo $tid; ?>">
        <section class="title-edit-card">
            <h2><?php echo h($tname); ?></h2>

            <div class="title-edit-field">
                <label for="titlename">Title name</label>
                <input id="titlename" type="text" name="titlename" maxlength="255" value="<?php echo h($tname); ?>" required>
            </div>

            <div class="title-edit-field">
                <span class="title-edit-label">Applies to</span>
                <div class="title-edit-options">
                    <label class="title-edit-option"><input type="radio" name="titletype" value="0" <?php echo $ttype === 0 ? 'checked' : ''; ?>> Account</label>
                    <label class="title-edit-option"><input type="radio" name="titletype" value="1" <?php echo $ttype === 1 ? 'checked' : ''; ?>> Character</label>
                </div>
            </div>

            <div class="title-edit-field">
                <label for="titlemaxrank">Maximum rank</label>
                <input id="titlemaxrank" type="number" name="titlemaxrank" min="1" max="15" value="<?php echo $tmr; ?>" required>
                <p class="title-edit-help">The number of ranks defined for this title track.</p>
            </div>

            <div class="title-edit-field">
                <span class="title-edit-label">Options</span>
                <div class="title-edit-options">
                    <label class="title-edit-option"><input type="checkbox" name="autofill" value="1" <?php echo $taf === 1 ? 'checked' : ''; ?>> Autofilled</label>
                    <label class="title-edit-option"><input type="checkbox" name="gwamm" value="1" <?php echo $tg === 1 ? 'checked' : ''; ?>> GWAMM title</label>
                </div>
            </div>

            <div class="title-edit-status">Current GWAMM title: <strong><?php echo h($gwamm ?? 'None'); ?></strong></div>

            <div class="title-edit-actions">
                <input class="title-edit-button" type="submit" value="Save changes">
                <a class="title-edit-back" href="titlemanager.php">Return to title manager</a>
            </div>

            <section class="title-edit-danger">
                <h3>Delete title</h3>
                <p>This removes the title when you save these changes. Only select this when you really intend to delete it.</p>
                <label class="title-edit-delete-option"><input type="checkbox" name="deltitle" value="yes"><span>Yes, delete this title</span></label>
            </section>
        </section>
    </form>
</section>
<?php
}
?>