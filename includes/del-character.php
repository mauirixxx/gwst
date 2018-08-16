<?php
if (isset($_SESSION['userid'])) {
    if ($delchar = $con->prepare("DELETE FROM gwchars WHERE charid = ? AND accid = ? AND userid = ?")) {
        $delchar->bind_param("iii", $delcharid, $delaccid, $_SESSION['userid']);
        for ($i = 0; $i < count($_POST['delcharid']); $i++) {
            $delcharid = $_POST['delcharid'][$i];
            $delaccid = $_POST['accid'][$i];
            $delchar->execute();
        }
        $delchar->close();
    }
    // need to delete associate character stats as well. TODO
    $nap = $con->prepare("UPDATE userinfo SET prefcharid = 0, prefcharname = 'No default selected' WHERE userid = ?");
    $nap->bind_param("i", $_SESSION['userid']);
    $nap->execute();
    $nap->close();
    $_SESSION['prefcharid'] = "0";
    $_SESSION['prefcharname'] = "No default selected";
    $_SESSION['charprofid'] = "0";
    echo 'Character(s) deleted - no preferred character selected.<br /><br />';
}
?>