<?php
if (isset($_SESSION['userid']) && isset($_SESSION['admin']) && (int)$_SESSION['admin'] === 1) {
    unset($_SESSION['title']);

    $titleId = isset($_SESSION['tid']) ? (int)$_SESSION['tid'] : filter_var($_POST['tid'] ?? null, FILTER_VALIDATE_INT);
    if ($titleId === false || $titleId < 1) {
        http_response_code(400);
        echo 'Invalid title selected.<br /><br />';
        echo 'Return to <a href="titlemanager.php" class="navlink">title manager</a>';
        return;
    }

    $stmtname = $con->prepare('SELECT titlename, titlemaxrank FROM gwtitles WHERE titlenameid = ?');
    $stmtname->bind_param('i', $titleId);
    $stmtname->execute();
    $titleResult = $stmtname->get_result();
    $titleRow = $titleResult->fetch_assoc();
    $stmtname->close();

    if (!$titleRow) {
        http_response_code(404);
        echo 'Title not found.<br /><br />';
        echo 'Return to <a href="titlemanager.php" class="navlink">title manager</a>';
        return;
    }

    $trank = $con->prepare('SELECT COALESCE(MAX(strank), 0) AS current_rank FROM gwsubtitles WHERE titlenameid = ?');
    $trank->bind_param('i', $titleId);
    $trank->execute();
    $rankRow = $trank->get_result()->fetch_assoc();
    $trank->close();
    $tr = ((int)$rankRow['current_rank']) + 1;
    $maxRank = (int)$titleRow['titlemaxrank'];

    echo 'Adding rank to title <b>' . h($titleRow['titlename']) . '</b><br />The maximum rank achievable in game is ' . $maxRank . '<br />';
    if ($tr > $maxRank) {
        echo '<br />No more ranks can be added!<br /><br />';
    } else {
        echo '<form action="titlemanager.php" method="post">' . csrf_input() . '<table border="1"><tr><th>Title Rank Name</th><th>Title Points</th><th>Rank Level</th></tr>';
        echo '<tr><td><input type="text" name="titlerankname" maxlength="255" required autofocus></td><td><input type="number" name="titlepoints" min="0" required></td><td><input type="number" readonly name="titlerank" min="1" max="' . $maxRank . '" value="' . $tr . '"></td></tr>';
        echo '</table><br /><input type="hidden" name="title" value="titleranksubmit"><input type="hidden" name="titlenameid" value="' . $titleId . '"><input type="submit" value="Add title rank ..."></form><br />';
    }

    echo 'Here are the currently associated title ranks, starting with rank 1:<br />';
    echo '<form action="titlemanager.php" method="post">' . csrf_input() . '<table border="1"><tr><th>stnameid</th><th>titlenameid</th><th>stname</th><th>stpoints</th><th>strank</th><th>Edit</th></tr>';
    $stmtview = $con->prepare('SELECT stnameid, titlenameid, stname, stpoints, strank FROM gwsubtitles WHERE titlenameid = ? ORDER BY strank ASC');
    $stmtview->bind_param('i', $titleId);
    $stmtview->execute();
    $result = $stmtview->get_result();
    while ($row = $result->fetch_assoc()) {
        echo '<tr><td>' . (int)$row['stnameid'] . '</td><td>' . (int)$row['titlenameid'] . '</td><td>' . h($row['stname']) . '</td><td>' . number_format((int)$row['stpoints']) . '</td><td>' . (int)$row['strank'] . '</td><td><input type="checkbox" name="editstitle[]" value="' . (int)$row['stnameid'] . '"></td></tr>';
    }
    $stmtview->close();

    $_SESSION['tid'] = $titleId;
    echo '</table><br /><input type="hidden" name="title" value="modsubtitle"><input type="submit" value="Edit selected titles"></form><br />If anything looks off, please fix it!<br /><br />';
    echo 'Return to <a href="titlemanager.php" class="navlink">title manager</a>';
}
?>