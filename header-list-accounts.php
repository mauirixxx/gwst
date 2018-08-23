<?php
if (isset($_SESSION['userid'])) {
    // $als = Account List Select
    $als = $con->prepare("SELECT accid, accemail FROM gwaccounts WHERE userid = ?");
    $als->bind_param("i", $_SESSION['userid']);
    $als->execute();
    $alsres = $als->get_result();
    while ($alsrow = $alsres->fetch_assoc()) {
        echo '<option value="' . $alsrow['accid'] . '">' . $alsrow['accemail'] . '</option>';
    }
    $als->close();
}
?>