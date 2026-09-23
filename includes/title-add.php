<?php
if (isset($_SESSION['userid']) && isset($_SESSION['admin']) && (int)$_SESSION['admin'] === 1) {
    echo '<b>Adding a new title</b><br /><br />';
    echo '<form action="titlemanager.php" method="post">' . csrf_input() . '<table border="1"><tr><th>Title Name</th><th>Title Type</th><th>Max Rank</th><th>Auto filled?</th><td>GWAMM?</td></tr>';
    echo '<tr><td><input type="text" name="titlename" maxlength="255" placeholder="Friend of the Kurzicks" required autofocus></td><td style="text-align:left"><input type="radio" name="titletype" value="0" checked> Account<br /><input type="radio" name="titletype" value="1">Character</td>';
    echo '<td><input type="number" name="titlemaxrank" min="0" max="15" required></td><td><input type="checkbox" name="autofill" value="1"></td><td><input type="checkbox" name="gwamm" value="1"></td></tr>';
    echo '</table><br /><input type="hidden" name="title" value="titlesubmit"><input type="submit" value="Add new title ..."></form>';
}
?>