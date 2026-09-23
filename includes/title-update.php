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
            echo 'Deleted title <b>' . h($title_row['titlename']) . '</b>, including its associated ranks and assigned title stats. Redirecting!';
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

    echo 'Title updated: <b>' . h($titlename) . '</b>. Redirecting!';
    header("Refresh:1; url=titlemanager.php");
}
?>