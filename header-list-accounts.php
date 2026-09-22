<?php

$header_accounts = array();

if (isset($_SESSION['userid'])) {
    // $als = Account List Select
    $als = $con->prepare("SELECT accid, accemail FROM gwaccounts WHERE userid = ? ORDER BY accemail");
    $als->bind_param("i", $_SESSION['userid']);
    $als->execute();
    $alsres = $als->get_result();

    while ($alsrow = $alsres->fetch_assoc()) {
        $header_accounts[] = $alsrow;
    }

    $als->close();
}
?>