<?php
if (isset($_SESSION['userid']) && isset($_SESSION['admin']) && (int)$_SESSION['admin'] === 1) {
    $titleId = filter_var($_SESSION['tid'] ?? null, FILTER_VALIDATE_INT);
    $selected = is_array($_POST['editstitle'] ?? null) ? $_POST['editstitle'] : [];
    $rankIds = array_values(array_unique(array_filter(
        array_map('intval', $selected),
        function ($id) { return $id > 0; }
    )));

    if ($titleId === false || $titleId < 1 || empty($rankIds)) {
        echo 'No valid title ranks selected! Please return to the title manager and try again.<br /><br />';
        echo 'Return to <a href="titlemanager.php" class="navlink">title manager</a>';
        return;
    }

    $placeholders = implode(',', array_fill(0, count($rankIds), '?'));
    $types = 'i' . str_repeat('i', count($rankIds));
    $params = array_merge([$titleId], $rankIds);

    $sredit = $con->prepare("SELECT stnameid, titlenameid, stname, stpoints, strank FROM gwsubtitles WHERE titlenameid = ? AND stnameid IN ($placeholders) ORDER BY strank ASC");
    $sredit->bind_param($types, ...$params);
    $sredit->execute();
    $result = $sredit->get_result();

    if ($result->num_rows === 0) {
        $sredit->close();
        echo 'No matching title ranks were found.<br /><br />';
        echo 'Return to <a href="titlemanager.php" class="navlink">title manager</a>';
        return;
    }

    echo '<form action="titlemanager.php" method="post">' . csrf_input() . '<table border="1"><caption>Deleting takes precedence over edits - edits will have to be remade after submission</caption>';
    echo '<tr><th>stnameid</th><th>titlenameid</th><th>stname</th><th>stpoints</th><th>strank</th><th>Delete?</th></tr>';

    while ($row = $result->fetch_assoc()) {
        echo '<tr><td><input type="text" readonly size="4" name="stnameid[]" value="' . (int)$row['stnameid'] . '"></td><td><input type="text" readonly size="4" name="titlenameid[]" value="' . (int)$row['titlenameid'] . '"></td>';
        echo '<td><input type="text" maxlength="255" name="stname[]" value="' . h($row['stname']) . '" required></td><td><input type="number" min="0" name="stpoints[]" value="' . (int)$row['stpoints'] . '" required></td>';
        echo '<td><input type="number" size="4" min="1" max="15" name="strank[]" value="' . (int)$row['strank'] . '" required></td><td><input type="checkbox" name="delsubtitle[]" value="' . (int)$row['stnameid'] . '"></td></tr>';
    }
    $sredit->close();

    echo '</table><br /><input type="hidden" name="title" value="updatesubtitle"><input type="submit" value="Modify title rank(s)"></form>';
    echo '<br /><br />';
    echo 'Return to <a href="titlemanager.php" class="navlink">title manager</a>';
}
?>