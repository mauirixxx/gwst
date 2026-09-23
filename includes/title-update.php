<?php
if (isset($_SESSION['userid']) && isset($_SESSION['admin']) && (int)$_SESSION['admin'] === 1) {
    $titlenameid = filter_input(INPUT_POST, 'titlenameid', FILTER_VALIDATE_INT);
    if (!$titlenameid || $titlenameid < 1) {
        http_response_code(400);
        echo 'Invalid title selected.';
        return;
    }

    if (isset($_POST['deltitle'])) {
        if ($_POST['deltitle'] === 'yes') {
            $stmtname = $con->prepare("SELECT titlename FROM gwtitles WHERE titlenameid = ? LIMIT 1");
            $stmtname->bind_param("i", $titlenameid);
            $stmtname->execute();
            $title_row = $stmtname->get_result()->fetch_assoc();
            $stmtname->close();
            if (!$title_row) {
                http_response_code(404);
                echo 'Title not found.';
                return;
            }

            echo '<form action="titlemanager.php" method="post">' . csrf_input();
            echo 'Please check the box to verify you want to delete: <b>' . h($title_row['titlename']) . '</b> ';
            echo '<input type="checkbox" name="deltitle" value="iamsure" required>';
            echo '<input type="hidden" name="titlenameid" value="' . $titlenameid . '">';
            echo '<input type="hidden" name="title" value="updatetitle"><input type="submit" value="Delete title"></form><br /><br />';
            return;
        }

        if ($_POST['deltitle'] === 'iamsure') {
            $stmtname = $con->prepare("SELECT titlename FROM gwtitles WHERE titlenameid = ? LIMIT 1");
            $stmtname->bind_param("i", $titlenameid);
            $stmtname->execute();
            $title_row = $stmtname->get_result()->fetch_assoc();
            $stmtname->close();
            if (!$title_row) {
                http_response_code(404);
                echo 'Title not found.';
                return;
            }

            $con->begin_transaction();
            try {
                $stmtdelstats = $con->prepare("DELETE FROM gwstats WHERE titlenameid = ?");
                $stmtdelstats->bind_param("i", $titlenameid);
                $stmtdelstats->execute();
                $stmtdelstats->close();
                $stmtdelst = $con->prepare("DELETE FROM gwsubtitles WHERE titlenameid = ?");
                $stmtdelst->bind_param("i", $titlenameid);
                $stmtdelst->execute();
                $stmtdelst->close();
                $stmtdel = $con->prepare("DELETE FROM gwtitles WHERE titlenameid = ?");
                $stmtdel->bind_param("i", $titlenameid);
                $stmtdel->execute();
                $stmtdel->close();
                $con->commit();
            } catch (Throwable $e) {
                $con->rollback();
                throw $e;
            }
            echo '<div class="preference-message">Deleted title <strong>' . h($title_row['titlename']) . '</strong>, including its ranks and assigned title stats. Returning to Title Manager…</div>';
            header("Refresh:1; url=titlemanager.php");
            return;
        }

        http_response_code(400);
        echo 'Invalid delete request.';
        return;
    }

    $titlename = trim((string)($_POST['titlename'] ?? ''));
    $titletype = filter_input(INPUT_POST, 'titletype', FILTER_VALIDATE_INT);
    $titlemaxrank = filter_input(INPUT_POST, 'titlemaxrank', FILTER_VALIDATE_INT);
    $autofill = isset($_POST['autofill']) ? 1 : 0;
    $gwamm = isset($_POST['gwamm']) ? 1 : 0;

    if ($titlename === '' || strlen($titlename) > 255 || !in_array($titletype, [0, 1], true) || $titlemaxrank === false || $titlemaxrank < 1 || $titlemaxrank > 15) {
        http_response_code(400);
        echo 'Invalid title settings.';
        return;
    }

    $con->begin_transaction();
    try {
        if ($gwamm === 1) {
            $rg = $con->prepare("UPDATE gwtitles SET gwamm = 0 WHERE gwamm = 1");
            $rg->execute();
            $rg->close();
        }
        $stmtupd = $con->prepare("UPDATE gwtitles SET titlename = ?, titletype = ?, titlemaxrank = ?, autofilled = ?, gwamm = ? WHERE titlenameid = ?");
        $stmtupd->bind_param("siiiii", $titlename, $titletype, $titlemaxrank, $autofill, $gwamm, $titlenameid);
        $stmtupd->execute();
        $stmtupd->close();
        $con->commit();
    } catch (Throwable $e) {
        $con->rollback();
        throw $e;
    }

    $type_label = $titletype === 0 ? 'Account' : 'Character';
    echo '<style>.title-update-confirm{width:min(100%,720px);margin:8px auto 28px;padding:24px;border:1px solid #2d6978;border-radius:8px;background:#122936}.title-update-confirm h1{margin:0 0 8px;color:#69dbe1}.title-update-confirm p{color:#b9d3df}.title-update-confirm dl{display:grid;grid-template-columns:150px 1fr;gap:8px 16px;margin:20px 0}.title-update-confirm dt{color:#9fb9c4;font-weight:700}.title-update-confirm dd{margin:0}.title-update-actions{display:flex;flex-wrap:wrap;gap:12px}.title-update-actions form{margin:0}.title-update-actions button,.title-update-actions a{display:inline-flex;align-items:center;min-height:42px;padding:8px 16px;border:1px solid #2999a5;border-radius:5px;background:#174454;color:#fff;font-weight:700;text-decoration:none;cursor:pointer}.title-update-actions a{border-color:#45606c;background:#182e39;color:#fff27a}</style>';
    echo '<section class="title-update-confirm"><h1>Title updated</h1><p>The settings for <strong>' . h($titlename) . '</strong> were saved successfully.</p>';
    echo '<dl><dt>Applies to</dt><dd>' . h($type_label) . '</dd><dt>Maximum rank</dt><dd>' . (int)$titlemaxrank . '</dd><dt>Autofilled</dt><dd>' . ($autofill ? 'Yes' : 'No') . '</dd><dt>GWAMM aggregate</dt><dd>' . ($gwamm ? 'Yes' : 'No') . '</dd></dl>';
    echo '<div class="title-update-actions"><form action="titlemanager.php" method="post">' . csrf_input() . '<input type="hidden" name="title" value="addsubtitle"><input type="hidden" name="tid" value="' . (int)$titlenameid . '"><button type="submit">Manage ranks &amp; points</button></form><a href="titlemanager.php">Return to Title Manager</a></div></section>';
}
?>