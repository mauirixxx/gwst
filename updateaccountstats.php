<?php
$pagetitle = "Update account wide stats";
include_once ('header.php');
if (isset($_SESSION['userid'])) {
    if (!isset($_POST['acctitle'])) {
        $_POST['acctitle'] = "notselected";
    }
    if ($_SESSION['prefaccid'] == "0") {
        echo 'Please select an account from the menu above to add stats to before continuing';
        include_once ('footer.php');
        exit();
    }
    if (isset($_POST['titlepoints'])) {
        include_once ('includes/update-titleranks.php');
    }

    echo '<section class="stats-update-page">';

    if ($_POST['acctitle'] == "notselected") {
        echo '<form action="updateaccountstats.php" method="post" class="stats-title-picker">';
        echo csrf_input();
        echo '<label for="acctitle">Select account title to update</label>';
        echo '<div class="stats-title-picker-row"><select id="acctitle" name="acctitle" onchange="this.form.submit()">';
        $ats = $con->prepare("SELECT titlenameid, titlename FROM gwtitles WHERE titletype = 0 ORDER BY titlename");
        $ats->execute();
        $result = $ats->get_result();
        while ($row = $result->fetch_assoc()) {
            echo '<option value="' . (int)$row['titlenameid'] . '">' . h($row['titlename']) . '</option>';
        }
        echo '</select><button type="submit">Select title</button></div></form>';
        $ats->close();
    } else {
        $selected_title = $con->prepare("SELECT titlename FROM gwtitles WHERE titlenameid = ? AND titletype = 0");
        $selected_title->bind_param("i", $_POST['acctitle']);
        $selected_title->execute();
        $selected_title->bind_result($selected_title_name);
        if (!$selected_title->fetch()) {
            $selected_title_name = 'Unknown title';
        }
        $selected_title->close();
        echo '<div class="stats-title-editor">';
        echo '<p>Updating account title: <strong>' . h($selected_title_name) . '</strong></p>';
        echo '<form action="updateaccountstats.php" method="post">' . csrf_input() . '<input type="hidden" name="titlenameid" value="' . (int)$_POST['acctitle'] .'">';
        echo '<input type="number" min="0" step="1" name="titlepoints" required autofocus><button type="submit">Update points</button></form>';
        echo '</div>';
    }
    echo '<div class="stats-current">Current account stats for: <strong>' . h($_SESSION['prefaccname']) . '</strong></div>';
    include_once ('includes/getaccountstats.php');
    echo '<p>Return to your <a href="index.php" class="navlink">user</a> page</p>';
    echo '</section>';

    echo '<style>
    .stats-update-page { width: min(100%, 900px); margin: 0 auto; }
    .stats-title-picker, .stats-title-editor { width: min(100%, 760px); margin: 18px auto 28px; padding: 20px 24px; border: 1px solid #2d6978; border-radius: 7px; background: #122936; }
    .stats-title-picker label { display: block; float: none; width: auto; margin: 0 0 10px; padding: 0; color: #69dbe1; font-size: 20px; font-weight: 600; text-align: left; }
    .stats-title-picker-row { display: grid; grid-template-columns: minmax(0, 1fr) auto; gap: 10px; align-items: center; }
    .stats-title-picker select, .stats-title-editor input[type="number"] { width: 100%; min-height: 42px; padding: 7px 12px; border: 1px solid #547080; border-radius: 5px; background: #edf2f5; color: #17242c; font: 16px "Segoe UI", Tahoma, Arial, sans-serif; }
    .stats-title-picker button, .stats-title-editor button { min-height: 42px; padding: 8px 16px; border: 1px solid #2999a5; border-radius: 5px; background: #174454; color: #fff; font-weight: 600; cursor: pointer; }
    .stats-title-picker button:hover, .stats-title-editor button:hover { background: #1b5668; }
    .stats-title-editor p { margin-top: 0; }
    .stats-title-editor form { display: grid; grid-template-columns: minmax(0, 1fr) auto; gap: 10px; }
    .stats-current { margin: 18px 0 10px; text-align: center; }
    @media (max-width: 600px) {
        .stats-title-picker-row, .stats-title-editor form { grid-template-columns: 1fr; }
        .stats-title-picker button, .stats-title-editor button { width: 100%; }
    }
    </style>';
}
include_once ('footer.php');
?>