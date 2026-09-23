<?php
if (isset($_SESSION['userid'])) {
    $raw_character_ids = $_POST['delcharid'] ?? [];
    if (!is_array($raw_character_ids)) {
        http_response_code(400);
        echo 'Invalid character selection.<br /><br />';
        return;
    }

    $character_ids = array_values(array_unique(array_filter(
        array_map('intval', $raw_character_ids),
        function ($id) { return $id > 0; }
    )));

    if (empty($character_ids)) {
        echo 'No characters selected for deletion.<br /><br />';
        return;
    }

    $prefaccid = (int)($_SESSION['prefaccid'] ?? 0);
    $userid = (int)$_SESSION['userid'];
    $placeholders = implode(',', array_fill(0, count($character_ids), '?'));
    $types = str_repeat('i', count($character_ids)) . 'ii';
    $params = array_merge($character_ids, [$prefaccid, $userid]);

    // Resolve the submitted IDs against characters actually owned by this user/account.
    $owned = $con->prepare("SELECT charid FROM gwchars WHERE charid IN ($placeholders) AND accid = ? AND userid = ?");
    $owned->bind_param($types, ...$params);
    $owned->execute();
    $owned_result = $owned->get_result();
    $owned_ids = [];
    while ($row = $owned_result->fetch_assoc()) {
        $owned_ids[] = (int)$row['charid'];
    }
    $owned->close();

    if (count($owned_ids) !== count($character_ids)) {
        http_response_code(400);
        echo 'One or more selected characters are invalid.<br /><br />';
        return;
    }

    $clear_preferred = in_array((int)($_SESSION['prefcharid'] ?? 0), $owned_ids, true);

    $con->begin_transaction();
    try {
        $dcs = $con->prepare("DELETE FROM gwstats WHERE charid IN ($placeholders) AND accid = ? AND userid = ?");
        $dcs->bind_param($types, ...$params);
        if (!$dcs->execute()) {
            throw new RuntimeException('Unable to delete character stats.');
        }
        $dcs->close();

        $delchar = $con->prepare("DELETE FROM gwchars WHERE charid IN ($placeholders) AND accid = ? AND userid = ?");
        $delchar->bind_param($types, ...$params);
        if (!$delchar->execute() || $delchar->affected_rows !== count($owned_ids)) {
            throw new RuntimeException('Unable to delete all selected characters.');
        }
        $delchar->close();

        if ($clear_preferred) {
            $ncp = $con->prepare("UPDATE userinfo SET prefcharid = 0, prefcharname = 'No default selected' WHERE userid = ?");
            $ncp->bind_param('i', $userid);
            if (!$ncp->execute()) {
                throw new RuntimeException('Unable to clear preferred character.');
            }
            $ncp->close();
        }

        $con->commit();
    } catch (Throwable $e) {
        $con->rollback();
        error_log('Character deletion failed for user ' . $userid . ': ' . $e->getMessage());
        http_response_code(500);
        echo 'Unable to delete the selected characters right now. Please try again.<br /><br />';
        return;
    }

    if ($clear_preferred) {
        $_SESSION['prefcharid'] = '0';
        $_SESSION['prefcharname'] = 'No default selected';
        $_SESSION['charprofid'] = '0';
        echo 'Character(s) deleted - no preferred character selected.<br /><br />';
    } else {
        echo 'Character(s) deleted.<br /><br />';
    }
}
?>