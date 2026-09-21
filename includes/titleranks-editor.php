<?php
if (isset($_SESSION['userid'])) {
    if (isset($_POST['editstitle'])) {
        echo '<form action="titlemanager.php" method="post"><table border="1"><caption>Deleting takes precedence over edits - edits will have to be remade after submission</caption>';
        echo '<tr><th>stnameid</th><th>titlenameid</th><th>stname</th><th>stpoints</th><th>strank</th><th>Delete?</th></tr>';

        $rank_ids = array_values(array_unique(array_filter(
            array_map('intval', $_POST['editstitle']),
            function ($id) { return $id > 0; }
        )));

        if (empty($rank_ids)) {
            echo '</table><br />No valid title ranks selected.<br /><br />';
            echo 'Return to <a href="titlemanager.php" class="navlink">title manager</a>';
            return;
        }

        $placeholders = implode(',', array_fill(0, count($rank_ids), '?'));
        $types = 'i' . str_repeat('i', count($rank_ids));
        $params = array_merge([(int)$_SESSION['tid']], $rank_ids);

        $sredit = $con->prepare("SELECT * FROM gwsubtitles WHERE titlenameid = ? AND stnameid IN ($placeholders)");
        $sredit->bind_param($types, ...$params);
        $sredit->execute();
        $result = $sredit->get_result();

        while ($row = $result->fetch_assoc()) {
            echo '<tr><td><input type="text" readonly size="4" name="stnameid[]" value="' . (int)$row['stnameid']. '"></td><td><input type="text" readonly size="4" name="titlenameid[]" value="' . (int)$row['titlenameid'] . '"></td>';
            echo '<td><input type="text" name="stname[]" value="' . h($row['stname']) . '"></td><td><input type="number" min="1" name="stpoints[]" value="' . (int)$row['stpoints'] . '"></td>';
            echo '<td><input type="number" size="4" min="1" max="15" name="strank[]" value="' . (int)$row['strank'] . '"></td><td><input type="checkbox" name="delsubtitle[]" value="' . (int)$row['stnameid'] . '"></td></tr>';
        }
        $sredit->close();

        echo '</table><br /><input type="hidden" name="title" value="updatesubtitle"><input type="submit" value="Modify title rank(s)"></form>';
        echo '<br /><br />';
        echo 'Return to <a href="titlemanager.php" class="navlink">title manager</a>';
    } else {
        echo 'No title ranks selected! Please press the back button on your browser to return to the previous page.<br /><br />';
        echo 'Return to <a href="titlemanager.php" class="navlink">title manager</a>';
    }
}
?>