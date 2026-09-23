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

    echo 'New title added: <b>' . h($titlename) . '</b><br /><br />';
    $stmtview = $con->prepare("SELECT titlenameid, titlename, titletype, titlemaxrank, autofilled FROM gwtitles WHERE titlenameid = ? LIMIT 1");
    $stmtview->bind_param("i", $new_title_id);
    $stmtview->execute();
    $result = $stmtview->get_result();
    if ($row = $result->fetch_assoc()) {
        echo '<table border="1"><tr><th>titleid</th><th>titlename</th><th>titletype</th><th>titlemaxrank</th><th>autofilled</th></tr>';
        echo '<tr><td>' . (int)$row['titlenameid'] . '</td><td>' . h($row['titlename']) . '</td><td>' . (int)$row['titletype'] . '</td><td>' . (int)$row['titlemaxrank'] . '</td><td>' . (int)$row['autofilled'] . '</td></tr></table><br />';
    }
    $stmtview->close();
    echo 'Return to <a href="titlemanager.php" class="navlink">title manager</a>';
}
?>