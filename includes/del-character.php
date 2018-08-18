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
    // $dcs = Delete Character Stats
    $gcharid = implode(", ", $_POST['delcharid']);
    $dcs = $con->prepare("DELETE FROM gwcharstats WHERE charid IN ($gcharid) AND accid = ? AND userid = ?");
    $dcs->bind_param("ii", $_SESSION['prefaccid'], $_SESSION['userid']);
    $dcs->execute();
    $dcs->close();
    // set preferred character to none
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