<?php
$pagetitle = "Update account wide stats";
include_once ('header.php');
if (isset($_SESSION['userid'])) {
    if (!isset($_POST['acctitle'])) {
        $_POST['acctitle'] = "notselected";
    }
    
    if (isset($_POST['titlepoints'])) {
        // include file just updates the database
        include_once ('includes/update-titleranks.php');
    }
    
    if ($_POST['acctitle'] == "notselected") {
        echo '<form action="updateaccountstats.php" method="post">';
        echo 'Select account title to update: <select name="acctitle">';
        // $ats = Account Title Select
        $ats = $con->prepare("SELECT titlenameid, titlename FROM gwtitles WHERE titletype = 0");
        $ats->execute();
        $result = $ats->get_result();
        while ($row = $result->fetch_assoc()) {
            echo '<option value="' . $row['titlenameid'] . '">' . $row['titlename'] . '</option>';
        }
        echo '</select><input type="submit" value="Select title"></form><br />';
        $ats->close();
    } else {
        echo '<form action="updateaccountstats.php" method="post"><input type="hidden" name="titlenameid" value="' . $_POST['acctitle'] .'">';
        echo '<input type="number" name="titlepoints" required autofocus><input type="submit" value="Update points"></form>';  
    }
    echo 'Current account stats for: <b>' . $_SESSION['prefaccname'] . '</b><br />';
    include_once ('includes/getaccountstats.php');
    echo 'Return to your <a href="index.php" class="navlink">user</a> page<br />';
}
include_once ('footer.php');
?>