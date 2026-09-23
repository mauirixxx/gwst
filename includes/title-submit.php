<?php
if (isset($_SESSION['userid']) && isset($_SESSION['admin']) && (int)$_SESSION['admin'] === 1) {
    $titlename = trim((string)($_POST['titlename'] ?? ''));
    $titletype = filter_input(INPUT_POST, 'titletype', FILTER_VALIDATE_INT);
    $titlemaxrank = filter_input(INPUT_POST, 'titlemaxrank', FILTER_VALIDATE_INT);
    $autofill = isset($_POST['autofill']) ? 1 : 0;
    $gwamm = isset($_POST['gwamm']) ? 1 : 0;

    if ($titlename === '' || strlen($titlename) > 255 || !in_array($titletype, [0, 1], true) || $titlemaxrank === false || $titlemaxrank < 0 || $titlemaxrank > 15) {
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

        $stmtins = $con->prepare("INSERT INTO gwtitles (titlename, titletype, titlemaxrank, autofilled, gwamm) VALUES (?, ?, ?, ?, ?)");
        $stmtins->bind_param("siiii", $titlename, $titletype, $titlemaxrank, $autofill, $gwamm);
        $stmtins->execute();
        $new_title_id = $con->insert_id;
        $stmtins->close();
        $con->commit();
    } catch (Throwable $e) {
        $con->rollback();
        throw $e;
    }

    $type_label = $titletype === 0 ? 'Account' : 'Character';
    echo '<style>.title-confirm{width:min(100%,760px);margin:8px auto 28px;padding:24px;border:1px solid #2d6978;border-radius:8px;background:#122936;text-align:left}.title-confirm h1{margin:0 0 8px;color:#69dbe1}.title-confirm p{color:#b9d3df}.title-confirm dl{display:grid;grid-template-columns:150px 1fr;gap:8px 16px;margin:20px 0}.title-confirm dt{color:#9fb9c4;font-weight:700}.title-confirm dd{margin:0;color:#f1f5f7}.title-confirm-actions{display:flex;flex-wrap:wrap;gap:12px;margin-top:22px}.title-confirm-actions form{margin:0}.title-confirm-actions button,.title-confirm-actions a{display:inline-flex;align-items:center;min-height:42px;padding:8px 16px;border:1px solid #2999a5;border-radius:5px;background:#174454;color:#fff;font-weight:700;text-decoration:none;cursor:pointer}.title-confirm-actions a.secondary{border-color:#45606c;background:#182e39;color:#fff27a}@media(max-width:520px){.title-confirm dl{grid-template-columns:1fr;gap:3px}.title-confirm dd{margin-bottom:8px}}</style>';
    echo '<section class="title-confirm"><h1>Title created</h1><p><strong>' . h($titlename) . '</strong> is now in GWTTT. Review the settings below, then add its rank names and point thresholds if needed.</p>';
    echo '<dl><dt>Title ID</dt><dd>' . (int)$new_title_id . '</dd><dt>Applies to</dt><dd>' . h($type_label) . '</dd><dt>Maximum rank</dt><dd>' . (int)$titlemaxrank . '</dd><dt>Autofilled</dt><dd>' . ($autofill ? 'Yes' : 'No') . '</dd><dt>GWAMM aggregate</dt><dd>' . ($gwamm ? 'Yes' : 'No') . '</dd></dl>';
    echo '<div class="title-confirm-actions">';
    echo '<form action="titlemanager.php" method="post">' . csrf_input() . '<input type="hidden" name="title" value="addsubtitle"><input type="hidden" name="tid" value="' . (int)$new_title_id . '"><button type="submit">Manage ranks &amp; points</button></form>';
    echo '<form action="titlemanager.php" method="post">' . csrf_input() . '<input type="hidden" name="title" value="modtitle"><input type="hidden" name="tid" value="' . (int)$new_title_id . '"><button type="submit">Edit title settings</button></form>';
    echo '<a class="secondary" href="titlemanager.php">Return to Title Manager</a></div></section>';
}
?>