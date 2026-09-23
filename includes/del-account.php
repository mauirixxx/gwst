<?php
if (isset($_SESSION['userid'])) {
    $raw_account_ids = $_POST['delaccid'] ?? [];
    if (!is_array($raw_account_ids)) {
        http_response_code(400);
        echo 'Invalid account selection.<br /><br />';
        return;
    }

    $account_ids = array_values(array_unique(array_filter(
        array_map('intval', $raw_account_ids),
        function ($id) { return $id > 0; }
    )));

    if (empty($account_ids)) {
        echo 'No accounts selected for deletion.<br /><br />';
        return;
    }

    $userid = (int)$_SESSION['userid'];
    $placeholders = implode(',', array_fill(0, count($account_ids), '?'));
    $types = str_repeat('i', count($account_ids)) . 'i';
    $params = array_merge($account_ids, [$userid]);

    // Resolve submitted IDs against accounts actually owned by this user.
    $owned = $con->prepare("SELECT accid FROM gwaccounts WHERE accid IN ($placeholders) AND userid = ?");
    $owned->bind_param($types, ...$params);
    $owned->execute();
    $owned_result = $owned->get_result();
    $owned_ids = [];
    while ($row = $owned_result->fetch_assoc()) {
        $owned_ids[] = (int)$row['accid'];
    }
    $owned->close();

    if (count($owned_ids) !== count($account_ids)) {
        http_response_code(400);
        echo 'One or more selected accounts are invalid.<br /><br />';
        return;
    }

    $clear_preferences = in_array((int)($_SESSION['prefaccid'] ?? 0), $owned_ids, true);

    $con->begin_transaction();
    try {
        // Remove all title stats for these accounts, including account-level stats and character stats.
        $delstats = $con->prepare("DELETE FROM gwstats WHERE accid IN ($placeholders) AND userid = ?");
        $delstats->bind_param($types, ...$params);
        if (!$delstats->execute()) {
            throw new RuntimeException('Unable to delete account stats.');
        }
        $delstats->close();

        $delchars = $con->prepare("DELETE FROM gwchars WHERE accid IN ($placeholders) AND userid = ?");
        $delchars->bind_param($types, ...$params);
        if (!$delchars->execute()) {
            throw new RuntimeException('Unable to delete account characters.');
        }
        $delchars->close();

        $delacc = $con->prepare("DELETE FROM gwaccounts WHERE accid IN ($placeholders) AND userid = ?");
        $delacc->bind_param($types, ...$params);
        if (!$delacc->execute() || $delacc->affected_rows !== count($owned_ids)) {
            throw new RuntimeException('Unable to delete all selected accounts.');
        }
        $delacc->close();

        if ($clear_preferences) {
            $clearprefs = $con->prepare("UPDATE userinfo SET prefaccid = 0, prefaccname = 'No default selected', prefcharid = 0, prefcharname = 'No default selected' WHERE userid = ?");
            $clearprefs->bind_param('i', $userid);
            if (!$clearprefs->execute()) {
                throw new RuntimeException('Unable to clear account preferences.');
            }
            $clearprefs->close();
        }

        $con->commit();
    } catch (Throwable $e) {
        $con->rollback();
        error_log('Account deletion failed for user ' . $userid . ': ' . $e->getMessage());
        http_response_code(500);
        echo 'Unable to delete the selected accounts right now. Please try again.<br /><br />';
        return;
    }

    if ($clear_preferences) {
        $_SESSION['prefaccid'] = '0';
        $_SESSION['prefaccname'] = 'No default selected';
        $_SESSION['prefcharid'] = '0';
        $_SESSION['prefcharname'] = 'No default selected';
        $_SESSION['charprofid'] = '0';
        echo 'Account(s) and related characters deleted - no preferred account or character selected.<br /><br />';
    } else {
        echo 'Account(s) and related characters deleted.<br /><br />';
    }
}
?>