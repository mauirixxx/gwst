<?php
$pagetitle = "Add a Guild Wars account to track";
include_once ('header.php');
if (isset($_SESSION['userid'])) {    
    if (!empty($_POST['prefcharid'])) {
        //this section contains code to the users preferred character
        include_once ('includes/set-prefchar.php');
    }
    if (!empty($_POST['prefaccid'])) {
        //this section contains code to set the users preferred game account
        include_once ('includes/set-prefacc.php');
    }
    if (!empty($_POST['accemail'])) {
        // this section contains the code to add a new game account to track
        include_once ('includes/addaccount-submit.php');
    }
    if (!empty($_POST['delaccid'])) {
        // this section containts the code to delete an account
        include_once ('includes/del-account.php');
    }
    if (!empty($_POST['delcharid'])) {
        // this section contains code to delete the selected characters
        include_once ('includes/del-character.php');
    }
    if (!empty($_POST['newcharname'])) {
        // this section contains code to insert a new character into the database
        include_once ('includes/addcharacters-submit.php');
    }
    $account_count_stmt = $con->prepare("SELECT COUNT(*) FROM gwaccounts WHERE userid = ?");
    $account_count_stmt->bind_param("i", $_SESSION['userid']);
    $account_count_stmt->execute();
    $account_count_stmt->bind_result($existing_account_count);
    $account_count_stmt->fetch();
    $account_count_stmt->close();

    $suggested_account_name = ((int)$existing_account_count === 0) ? $_SESSION['usermail'] : '';

    echo '<form action="addaccounts.php" method="post"><table>';
    echo '<caption>Add a new Guild Wars account e-mail or alias</caption>';
    if ((int)$existing_account_count === 0) {
        echo '<tr><td colspan="2">Your signup e-mail is suggested below. Keep it, replace it with your Guild Wars login e-mail, or use an alias.</td></tr>';
    }
    echo '<tr><td><input type="text" name="accemail" size="35" value="' . h($suggested_account_name) . '" required></td><td><input type="submit" value="Add account"></td></tr>';
    echo '</table></form><br />';
    echo '<form action="addaccounts.php" method="post"><table border="1"><caption style="white-space: nowrap; overflow: hidden;">Current Guild Wars accounts</caption>';
    echo '<tr><th>accid</th><th>Account name</th><th>Preferred?</th><th>Delete ?</th></tr>';
    $acclist = $con->prepare("SELECT accid, accemail FROM gwaccounts WHERE userid = ?");
    $acclist->bind_param("i", $_SESSION['userid']);
    $acclist->execute();
    $result = $acclist->get_result();
    while ($row = $result->fetch_assoc()) {
        echo '<tr><td><input type="text" readonly size="4" value="' . $row['accid'] . '"></td><td><form action="addaccounts.php" method="post"><input type="submit" class="submitLink warrior-yellow" value="' . h($row['accemail']) . '">';
        echo '</td><td><div class="radio"><input type="radio" name="prefaccid" value="'. $row['accid'] . '"';
        if ($row['accid'] == $_SESSION['prefaccid']) {
            echo ' checked';
        }
		//delete account array in delaccid[]
        echo '></div></td><td><input type="checkbox" name="delaccid[]" value="' . $row['accid'] . '"></td></tr>';
    }
    $acclist->close();
    echo '</form></table><input type="submit" value="Modify selected accounts"></form><br />';
    // Add characters only when the selected account belongs to this user.
    $selected_account_id = (int)($_SESSION['prefaccid'] ?? 0);
    $selected_account = null;
    if ($selected_account_id > 0) {
        $ownacc = $con->prepare("SELECT accid, accemail FROM gwaccounts WHERE accid = ? AND userid = ? LIMIT 1");
        $ownacc->bind_param("ii", $selected_account_id, $_SESSION['userid']);
        $ownacc->execute();
        $selected_account = $ownacc->get_result()->fetch_assoc();
        $ownacc->close();
    }

    if (!$selected_account) {
        echo '<b>Add and select a Guild Wars account before adding characters.</b><br /><br />';
    } else {
    echo '<form action="addaccounts.php" method="post"><table>';
    echo '<caption style="white-space: nowrap; overflow: hidden;">Add character to account: ' . h($selected_account['accemail']) . '</caption>';
    echo '<tr><th>Character name</th><th>Birthdate</th><th>Profession</th></tr>';
    echo '<tr><td><input type="text" name="newcharname" size="19" required autofocus></td><td><input type="date" name="bdate" placeholder="2005-04-28"></td><td><select name="profid" required>';
    // $gp = Get Profession
    $gp = $con->prepare("SELECT profid, profession FROM gwprofessions");
    $gp->execute();
    $result = $gp->get_result();
    while ($row = $result->fetch_assoc()) {
        echo '<option value=' . $row['profid'] . '>' . $row['profession'] . '</option>';
    }
    echo '</td></tr>';
    echo '<tr><td colspan="3"><input type="submit" value="Add character"></td></tr></table></form><br />';
    }
    echo '<form action="addaccounts.php" method="post"><table border="1"><caption style="white-space: nowrap; overflow: hidden;">Available characters</caption>';
    echo '<tr><td>charid</td><td>accid</td><td>charname</td><td>Preferred</td><td>Delete?</td></tr>';
    $lc = $con->prepare("SELECT charid, accid, charname, profid, profcolor FROM gwchars WHERE accid = ? AND userid = ?");
    $lc->bind_param("ii", $_SESSION['prefaccid'], $_SESSION['userid']);
    $lc->execute();
    $res2 = $lc->get_result();
    while ($row2 = $res2->fetch_assoc()) {
        echo '<tr><td><input type="text" readonly size="4" name="charid[]" value="' . $row2['charid'] . '"></td>';
        echo '<td><input type="text" readonly size="4" name="accid[]" value="' . $row2['accid'] . '"></td>';
        echo '<td style="background-color:' . h($row2['profcolor']) . '"><a class="submitLink" href="editcharacter.php?charid=' . (int)$row2['charid'] . '">' . h($row2['charname']) . '</a></td>';
        echo '<td><div class="radio"><input type="radio" name="prefcharid" value="' . $row2['charid'] . '"';
        if ($row2['charid'] == $_SESSION['prefcharid']) {
            echo ' checked';
        }
        echo '></div></td>';
        echo '<td><input type="checkbox" name="delcharid[]" value="' . $row2['charid'] . '"></td></tr>';
    }
    echo '</form></table><input type="submit" value="Modify selected characters"></form><br />';
    echo '<br />Return to your <a href="index.php" class="navlink">user</a> page';
}
include_once ('footer.php');
?>