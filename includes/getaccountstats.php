<?php
echo '<table border="1"><caption>Account wide stats</caption>';
echo '<tr><th>Title Name</th><th>Title Points</th><th>Current Rank</th><th>Points Remaining</th><th>Next Rank</th></tr>';
// $gas = GetAccountStats
$gas = $con->prepare("SELECT * FROM gwaccstats WHERE userid = ? AND accid = ?;");
$gas->bind_param("ii", $_SESSION['prefaccid'], $_SESSION['userid']);
$gas->execute();
$result = $gas->get_result();
while ($row = $result->fetch_assoc()) {
    echo '<tr><td>' . $row['currentstrankname'] . '</td><td>' . $row['titlepoints'] . '</td><td>' . $row['currentstrank'] . '</td>';
    echo '<td>';
    $pr = ($row['nextstrankpoints'] - $row['titlepoints']);
    if ($pr < 0) {
        echo 'Maximum rank achieved!';
    } else {
        echo $pr;
    }
    echo '</td>';
    echo '<td>' . $row['nextstrank'] . '</td></tr>';
}
$gas->close();
echo '</table>';
?>