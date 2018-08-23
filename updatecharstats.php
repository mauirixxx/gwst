<?php
$pagetitle = "Update character stats";
include_once ('header.php');
if (isset($_SESSION['userid'])) {
    if (!isset($_POST['chartitle'])) {
        $_POST['chartitle'] = "notselected";
    }
    if ($_SESSION['prefcharid'] == "0") {
        echo 'Please select a character from the menu above to add stats to before continuing';
        include_once ('footer.php');
        exit();
    }
    if (isset($_POST['titlepoints'])) {
        // include file just updates the database
        include_once ('includes/update-chartitleranks.php');
    }
    
    if ($_POST['chartitle'] == "notselected") {
        echo '<form action="updatecharstats.php" method="post">';
        echo 'Select character title to update: <select name="chartitle" onchange="this.form.submit()">';
        // $cts = Character Title Select
        $cts = $con->prepare("SELECT titlenameid, titlename FROM gwtitles WHERE titletype = 1 AND autofilled = 0 ORDER BY titlename");
        $cts->execute();
        $result = $cts->get_result();
        while ($row = $result->fetch_assoc()) {
            echo '<option value="' . $row['titlenameid'] . '">' . $row['titlename'] . '</option>';
        }
        echo '</select><input type="submit" value="Select title"></form><br />';
        $cts->close();
    } else {
        echo '<form action="updatecharstats.php" method="post"><input type="hidden" name="titlenameid" value="' . $_POST['chartitle'] .'">';
        echo '<input type="number" step="0.1" name="titlepoints" required autofocus><noscript><input type="submit" value="Update points"></noscript></form>';  
    }
    echo 'Current character stats for: <b>' . $_SESSION['prefcharname'] . '</b><br />';
    include_once ('includes/getcharstats.php');
    echo 'Return to your <a href="index.php" class="navlink">user</a> page<br />';
}
include_once ('footer.php');
?>