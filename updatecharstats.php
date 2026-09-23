<?php
$pagetitle = "Update character stats";
include_once ('header.php');
if (isset($_SESSION['userid'])) {
    if (!isset($_POST['chartitle'])) { $_POST['chartitle'] = "notselected"; }
    if ($_SESSION['prefcharid'] == "0") {
        echo 'Please select a character from the menu above to add stats to before continuing';
        include_once ('footer.php'); exit();
    }
    if (isset($_POST['titlepoints'])) { include_once ('includes/update-chartitleranks.php'); }

    echo '<section class="stats-update-page">';
    if ($_POST['chartitle'] == "notselected") {
        echo '<form action="updatecharstats.php" method="post" class="stats-title-picker">';
        echo csrf_input();
        echo '<label for="chartitle">Select character title to update</label>';
        echo '<div class="stats-title-picker-row"><select id="chartitle" name="chartitle" onchange="this.form.submit()">';
        $cts = $con->prepare("SELECT titlenameid, titlename FROM gwtitles WHERE titletype = 1 AND autofilled = 0 ORDER BY titlename");
        $cts->execute(); $result = $cts->get_result();
        while ($row = $result->fetch_assoc()) { echo '<option value="' . (int)$row['titlenameid'] . '">' . h($row['titlename']) . '</option>'; }
        echo '</select><button type="submit">Select title</button></div></form>'; $cts->close();
    } else {
        $selected_title = $con->prepare("SELECT titlename FROM gwtitles WHERE titlenameid = ? AND titletype = 1 AND autofilled = 0");
        $selected_title->bind_param("i", $_POST['chartitle']); $selected_title->execute(); $selected_title->bind_result($selected_title_name);
        if (!$selected_title->fetch()) { $selected_title_name = 'Unknown title'; }
        $selected_title->close();
        echo '<div class="stats-title-editor">';
        echo '<p>Updating character title: <strong>' . h($selected_title_name) . '</strong></p>';
        echo '<form action="updatecharstats.php" method="post">' . csrf_input() . '<input type="hidden" name="titlenameid" value="' . (int)$_POST['chartitle'] .'">';
        echo '<input type="number" min="0" step="1" name="titlepoints" required autofocus><button type="submit">Update points</button></form></div>';
    }
    echo '<div class="stats-current">Current character stats for: <strong>' . h($_SESSION['prefcharname']) . '</strong></div>';
    include_once ('includes/getcharstats.php');
    echo '<p>Return to your <a href="index.php" class="navlink">user</a> page</p></section>';

    echo '<style>
    .stats-update-page { width: max-content; min-width: min(100%, 900px); margin: 0 auto; }
    .stats-update-page .stats-card { width: max-content; min-width: 100%; }
    .stats-update-page .stats-table { width: max-content; min-width: 100%; }
    .stats-update-page .stats-table th, .stats-update-page .stats-table td { white-space: nowrap; }
    .stats-title-picker, .stats-title-editor { width: min(100%, 760px); margin: 18px auto 28px; padding: 20px 24px; border: 1px solid #2d6978; border-radius: 7px; background: #122936; }
    .stats-title-picker label { display: block; float: none; width: auto; margin: 0 0 10px; padding: 0; color: #69dbe1; font-size: 20px; font-weight: 600; text-align: left; }
    .stats-title-picker-row { display: grid; grid-template-columns: minmax(0, 1fr) auto; gap: 10px; align-items: center; }
    .stats-title-picker select, .stats-title-editor input[type="number"] { width: 100%; min-height: 42px; padding: 7px 12px; border: 1px solid #547080; border-radius: 5px; background: #edf2f5; color: #17242c; font: 16px "Segoe UI", Tahoma, Arial, sans-serif; }
    .stats-title-picker button, .stats-title-editor button { min-height: 42px; padding: 8px 16px; border: 1px solid #2999a5; border-radius: 5px; background: #174454; color: #fff; font-weight: 600; cursor: pointer; }
    .stats-title-picker button:hover, .stats-title-editor button:hover { background: #1b5668; }
    .stats-title-editor p { margin-top: 0; }
    .stats-title-editor form { display: grid; grid-template-columns: minmax(0, 1fr) auto; gap: 10px; }
    .stats-current { margin: 18px 0 10px; text-align: center; }
    @media (max-width: 600px) { .stats-title-picker-row, .stats-title-editor form { grid-template-columns: 1fr; } .stats-title-picker button, .stats-title-editor button { width: 100%; } }
    </style>';
}
include_once ('footer.php');
?>