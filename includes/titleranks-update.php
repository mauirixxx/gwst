<?php
if (isset($_SESSION['userid']) && isset($_SESSION['admin']) && (int)$_SESSION['admin'] === 1) {
    $titleId = filter_var($_SESSION['tid'] ?? null, FILTER_VALIDATE_INT);
    if ($titleId === false || $titleId < 1) {
        http_response_code(400);
        echo 'Invalid title selection.<br /><br />';
        echo 'Return to <a href="titlemanager.php" class="navlink">title manager</a>';
        return;
    }

    $titleStmt = $con->prepare('SELECT titlemaxrank FROM gwtitles WHERE titlenameid = ?');
    $titleStmt->bind_param('i', $titleId);
    $titleStmt->execute();
    $titleRow = $titleStmt->get_result()->fetch_assoc();
    $titleStmt->close();
    if (!$titleRow) {
        http_response_code(404);
        echo 'Title not found.<br /><br />';
        echo 'Return to <a href="titlemanager.php" class="navlink">title manager</a>';
        return;
    }
    $maxRank = (int)$titleRow['titlemaxrank'];

    if (isset($_POST['delsubtitle'])) {
        $deleteIds = is_array($_POST['delsubtitle']) ? $_POST['delsubtitle'] : [];
        $deleteIds = array_values(array_unique(array_filter(array_map('intval', $deleteIds), function ($id) { return $id > 0; })));
        if (empty($deleteIds)) {
            http_response_code(400);
            echo 'No valid title ranks selected for deletion.<br /><br />';
            return;
        }

        $delst = $con->prepare('DELETE FROM gwsubtitles WHERE titlenameid = ? AND stnameid = ?');
        $rankId = 0;
        $delst->bind_param('ii', $titleId, $rankId);
        $con->begin_transaction();
        try {
            foreach ($deleteIds as $rankId) {
                $delst->execute();
            }
            $con->commit();
        } catch (Throwable $e) {
            $con->rollback();
            $delst->close();
            throw $e;
        }
        $delst->close();
        echo 'Title rank(s) have been deleted, redirecting!';
        header('Refresh:1; url=titlemanager.php');
        return;
    }

    $names = is_array($_POST['stname'] ?? null) ? $_POST['stname'] : [];
    $points = is_array($_POST['stpoints'] ?? null) ? $_POST['stpoints'] : [];
    $ranks = is_array($_POST['strank'] ?? null) ? $_POST['strank'] : [];
    $rankIds = is_array($_POST['stnameid'] ?? null) ? $_POST['stnameid'] : [];
    $postedTitleIds = is_array($_POST['titlenameid'] ?? null) ? $_POST['titlenameid'] : [];
    $count = count($names);

    if ($count === 0 || count($points) !== $count || count($ranks) !== $count
        || count($rankIds) !== $count || count($postedTitleIds) !== $count) {
        http_response_code(400);
        echo 'Malformed title rank update.<br /><br />';
        return;
    }

    $updates = [];
    for ($i = 0; $i < $count; $i++) {
        $name = trim((string)$names[$i]);
        $pointValue = filter_var($points[$i], FILTER_VALIDATE_INT);
        $rankValue = filter_var($ranks[$i], FILTER_VALIDATE_INT);
        $rankId = filter_var($rankIds[$i], FILTER_VALIDATE_INT);
        $postedTitleId = filter_var($postedTitleIds[$i], FILTER_VALIDATE_INT);

        if ($name === '' || mb_strlen($name) > 255 || $pointValue === false || $pointValue < 0
            || $rankValue === false || $rankValue < 1 || $rankValue > $maxRank
            || $rankId === false || $rankId < 1 || $postedTitleId !== $titleId) {
            http_response_code(400);
            echo 'Invalid title rank data.<br /><br />';
            return;
        }
        $updates[] = [$name, $pointValue, $rankValue, $rankId];
    }

    $upd = $con->prepare('UPDATE gwsubtitles SET stname = ?, stpoints = ?, strank = ? WHERE titlenameid = ? AND stnameid = ?');
    $stname = '';
    $stpoints = 0;
    $strank = 0;
    $stnameid = 0;
    $upd->bind_param('siiii', $stname, $stpoints, $strank, $titleId, $stnameid);

    $con->begin_transaction();
    try {
        foreach ($updates as $update) {
            [$stname, $stpoints, $strank, $stnameid] = $update;
            $upd->execute();
            if ($upd->affected_rows === 0) {
                $check = $con->prepare('SELECT 1 FROM gwsubtitles WHERE titlenameid = ? AND stnameid = ?');
                $check->bind_param('ii', $titleId, $stnameid);
                $check->execute();
                $exists = $check->get_result()->fetch_row();
                $check->close();
                if (!$exists) {
                    throw new RuntimeException('Title rank no longer exists.');
                }
            }
        }
        $con->commit();
    } catch (Throwable $e) {
        $con->rollback();
        $upd->close();
        http_response_code(409);
        echo 'The title rank list changed while it was being edited. Please reload and try again.<br /><br />';
        return;
    }
    $upd->close();

    echo 'Title rank(s) updated, redirecting!';
    header('Refresh:1; url=titlemanager.php');
}
?>