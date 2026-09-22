<?php

$header_characters = array();

if (isset($_SESSION['userid']) && !empty($_SESSION['prefaccid'])) {
    // $cls = Character List Select
    $cls = $con->prepare("SELECT charid, charname, profid FROM gwchars WHERE accid = ? AND userid = ? ORDER BY charname");
    $cls->bind_param("ii", $_SESSION['prefaccid'], $_SESSION['userid']);
    $cls->execute();
    $clsres = $cls->get_result();

    while ($clsrow = $clsres->fetch_assoc()) {
        $header_characters[] = $clsrow;
    }

    $cls->close();
}
?>