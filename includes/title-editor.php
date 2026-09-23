<?php
if (isset($_SESSION['userid']) && isset($_SESSION['admin']) && (int)$_SESSION['admin'] === 1) {
    $tid_request = filter_input(INPUT_POST, 'tid', FILTER_VALIDATE_INT);
    if (!$tid_request || $tid_request < 1) {
        http_response_code(400);
        echo 'Invalid title selected.';
        return;
    }

    echo '<form action="titlemanager.php" method="post">' . csrf_input();
    echo '<table border="1"><tr><th>titlenameid</th><th>titlename</th><th>titletype</th><th>titlemaxrank</th><th>autofilled</th><th>gwamm</th></tr>';
    $stmtview = $con->prepare("SELECT * FROM gwtitles WHERE titlenameid = ?");
    $stmtview->bind_param("i", $tid_request);
    $stmtview->execute();
    $result = $stmtview->get_result();
    while ($row = $result->fetch_assoc()) {
        $tid = (int)$row['titlenameid'];
        $tname = $row['titlename'];
        $ttype = (int)$row['titletype'];
        $tmr = (int)$row['titlemaxrank'];
        $taf = (int)$row['autofilled'];
        $tg = (int)$row['gwamm'];
        echo '<b>Editing title: ' . h($tname) . '</b><br /><br />';
        echo '<tr><td><input readonly size="3" name="titlenameid" value="' . $tid . '"></td><td><input size="40" type="text" name="titlename" maxlength="255" value="' . h($tname) . '" required></td><td style="text-align:left">';
        echo '<input type="radio" name="titletype" ' . ($ttype === 0 ? 'checked ' : '') . 'value="0">Account<br />';
        echo '<input type="radio" name="titletype" ' . ($ttype === 1 ? 'checked ' : '') . 'value="1">Character</td>';
        echo '<td><input type="number" name="titlemaxrank" min="1" max="15" value="' . $tmr . '" required></td>';
        echo '<td><input type="checkbox" name="autofill" value="1" ' . ($taf === 1 ? 'checked' : '') . '></td>';
        echo '<td><input type="checkbox" name="gwamm" value="1" ' . ($tg === 1 ? 'checked' : '') . '></td></tr>';
    }
    $stmtview->close();
    echo '</table><table><tr><td>The current GWAMM title is: <b>';
    $ggt = $con->prepare("SELECT titlename FROM gwtitles WHERE gwamm = 1 LIMIT 1");
    $ggt->execute();
    $ggt->bind_result($gwamm);
    $ggt->fetch();
    $ggt->close();
    echo h($gwamm ?? 'None') . '</b></td></tr>';
    echo '<tr><th>Delete title?</th></tr><tr><td><input type="checkbox" name="deltitle" value="yes"></td></tr></table><br /><br />';
    echo '<input type="hidden" name="title" value="updatetitle"><input type="submit" value="Modify title ..."></form><br />';
    echo 'Return to <a href="titlemanager.php" class="navlink">title manager</a>';
}
?>