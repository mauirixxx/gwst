<?php
if (isset($_SESSION['userid']) && isset($_SESSION['admin']) && (int)$_SESSION['admin'] === 1) {
    $titleId = filter_var($_POST['titlenameid'] ?? null, FILTER_VALIDATE_INT);
    $rankName = trim((string)($_POST['titlerankname'] ?? ''));
    $titlePoints = filter_var($_POST['titlepoints'] ?? null, FILTER_VALIDATE_INT);
    $titleRank = filter_var($_POST['titlerank'] ?? null, FILTER_VALIDATE_INT);

    if ($titleId === false || $titleId < 1 || $rankName === '' || mb_strlen($rankName) > 255
        || $titlePoints === false || $titlePoints < 0
        || $titleRank === false || $titleRank < 1) {
        http_response_code(400);
        echo 'Invalid title rank data.<br /><br />';
        echo 'Return to <a href="titlemanager.php" class="navlink">title manager</a>';
        return;
    }

    // The submitted title ID and rank number are untrusted form data. Verify the
    // title exists and derive the next legal rank from the database instead.
    $stmtTitle = $con->prepare('SELECT titlename, titlemaxrank FROM gwtitles WHERE titlenameid = ?');
    $stmtTitle->bind_param('i', $titleId);
    $stmtTitle->execute();
    $titleResult = $stmtTitle->get_result();
    $titleRow = $titleResult->fetch_assoc();
    $stmtTitle->close();

    if (!$titleRow) {
        http_response_code(404);
        echo 'Title not found.<br /><br />';
        echo 'Return to <a href="titlemanager.php" class="navlink">title manager</a>';
        return;
    }

    $maxRank = (int)$titleRow['titlemaxrank'];
    $stmtRank = $con->prepare('SELECT COALESCE(MAX(strank), 0) AS current_rank FROM gwsubtitles WHERE titlenameid = ?');
    $stmtRank->bind_param('i', $titleId);
    $stmtRank->execute();
    $rankRow = $stmtRank->get_result()->fetch_assoc();
    $stmtRank->close();
    $expectedRank = ((int)$rankRow['current_rank']) + 1;

    if ($expectedRank > $maxRank || $titleRank !== $expectedRank) {
        http_response_code(409);
        echo 'The title rank list changed or this rank is no longer valid. Please reload the title manager and try again.<br /><br />';
        echo 'Return to <a href="titlemanager.php" class="navlink">title manager</a>';
        return;
    }

    $stmtstins = $con->prepare('INSERT INTO gwsubtitles (titlenameid, stname, stpoints, strank) VALUES (?, ?, ?, ?)');
    $stmtstins->bind_param('isii', $titleId, $rankName, $titlePoints, $expectedRank);
    $stmtstins->execute();
    $stmtstins->close();

    $_SESSION['title'] = 'repeat';
    $_SESSION['tid'] = $titleId;
    $_SESSION['tr'] = $expectedRank;

    echo 'Added rank <b>' . h($rankName) . '</b> to <b>' . h($titleRow['titlename']) . '</b>, redirecting!';
    header('Refresh:1; url=titlemanager.php');
}
?>