<?php
if (isset($_SESSION['userid'])) {
    $character_ids = array_values(array_unique(array_filter(
        array_map('intval', $_POST['delcharid'] ?? []),
        function ($id) { return $id > 0; }
    )));

    if (empty($character_ids)) {
        echo 'No characters selected for deletion.<br /><br />';
        return;
    }

    $placeholders = implode(',', array_fill(0, count($character_ids), '?'));
    $types = str_repeat('i', count($character_ids)) . 'ii';
    $params = array_merge($character_ids, [(int)$_SESSION['prefaccid'], (int)$_SESSION['userid']]);

    // Delete only characters owned by the logged-in user under the selected account.
    $delchar = $con->prepare("DELETE FROM gwchars WHERE charid IN ($placeholders) AND accid = ? AND userid = ?");
    $delchar->bind_param($types, ...$params);
    $delchar->execute();
    $delchar->close();

    // Delete title stats for the same owned characters.
    $dcs = $con->prepare("DELETE FROM gwstats WHERE charid IN ($placeholders) AND accid = ? AND userid = ?");
    $dcs->bind_param($types, ...$params);
    $dcs->execute();
    $dcs->close();

    // Set preferred character to none.
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