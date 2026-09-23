<?php
$pagetitle = "Title Editor";
include_once ('header.php');
if (isset($_SESSION['userid'])){
    if (isset($_SESSION['title'])) {
        if ($_SESSION['title'] == "repeat") {
            $_POST['title'] = "addsubtitle";
            unset($_SESSION['title']);
        } else {
            unset($_SESSION['tr']);
        }
    }
    if ($_SESSION['admin'] == 1) {
        if (isset($_POST['title'])) {
            if ($_POST['title'] == "addtitle") {
                include_once ('includes/title-add.php');
            } else if ($_POST['title'] == "titlesubmit") {
                include_once ('includes/title-submit.php');
            } else if ($_POST['title'] == "modtitle") {
                include_once ('includes/title-editor.php');
            } else if ($_POST['title'] == "updatetitle") {
                include_once ('includes/title-update.php');
            } else if ($_POST['title'] == "addsubtitle") {
                include_once ('includes/titleranks-add.php');
            } else if ($_POST['title'] == "titleranksubmit") {
                include_once ('includes/titleranks-submit.php');
            } else if ($_POST['title'] == "modsubtitle") {
                include_once ('includes/titleranks-editor.php');
            } else if ($_POST['title'] == "updatesubtitle") {
                include_once ('includes/titleranks-update.php');
            }
        } else {
            unset($_SESSION['tid']);
            unset($_SESSION['tr']);
            ?>
            <style>
            .title-manager-page { width: min(100%, 1080px); margin: 0 auto; padding: 8px 0 28px; text-align: left; }
            .title-manager-heading { margin: 4px 0 24px; text-align: center; }
            .title-manager-heading h1 { margin: 0 0 7px; }
            .title-manager-heading p { margin: 0; color: #9fb9c4; }
            .title-manager-tools { display: grid; grid-template-columns: minmax(210px, .75fr) minmax(0, 1.125fr) minmax(0, 1.125fr); gap: 14px; margin-bottom: 30px; }
            .title-manager-card { min-width: 0; padding: 18px; border: 1px solid #2d6978; border-radius: 7px; background: #122936; }
            .title-manager-card h2 { margin: 0 0 7px; color: #69dbe1; font-size: 18px; }
            .title-manager-card p { min-height: 38px; margin: 0 0 15px; color: #9fb9c4; font-size: 13px; }
            .title-manager-card form { width: 100%; margin: 0; }
            .title-manager-card select { width: 100%; min-width: 0; max-width: 100%; min-height: 40px; padding: 7px 34px 7px 10px; border: 1px solid #547080; border-radius: 5px; background: #edf2f5; color: #17242c; font: 15px "Segoe UI", Tahoma, Arial, sans-serif; }
            .title-manager-button { min-height: 40px; padding: 8px 16px; border: 1px solid #2999a5; border-radius: 5px; background: #174454; color: #fff; font: 600 15px "Segoe UI", Tahoma, Arial, sans-serif; cursor: pointer; }
            .title-manager-button:hover { background: #1b5668; }
            .title-manager-recent { border: 1px solid #294b5b; border-radius: 8px; overflow: hidden; background: #101f28; box-shadow: 0 12px 30px rgba(0,0,0,.22); }
            .title-manager-recent-header { padding: 18px 20px; border-bottom: 2px solid #28b8c0; background: #0d1c23; }
            .title-manager-recent-header h2 { margin: 0 0 4px; color: #dce8ee; font-size: 20px; }
            .title-manager-recent-header p { margin: 0; color: #9fb9c4; font-size: 13px; }
            .title-manager-table-wrap { width: 100%; overflow-x: auto; }
            .title-manager-table { width: 100%; border-collapse: collapse; background: #11232d; }
            .title-manager-table th { padding: 12px 10px; border-right: 1px solid #31505f; border-bottom: 1px solid #416170; background: linear-gradient(180deg,#1c3b4c,#183241); color: #f1f5f7; font-size: 12px; text-transform: uppercase; letter-spacing: .03em; white-space: nowrap; }
            .title-manager-table td { padding: 11px 10px; border-right: 1px solid #294754; border-bottom: 1px solid #294754; color: #e1edf1; font-size: 14px; text-align: center; }
            .title-manager-table td.title-name { text-align: left; font-weight: 600; color: #f0f5f6; }
            .title-manager-table tbody tr:nth-child(even) td { background: rgba(255,255,255,.018); }
            .title-manager-table tbody tr:hover td { background: rgba(40,184,192,.07); }
            .title-manager-table th:last-child, .title-manager-table td:last-child { border-right: 0; }
            .title-manager-table tbody tr:last-child td { border-bottom: 0; }
            .title-manager-badge { display: inline-block; min-width: 66px; padding: 3px 8px; border: 1px solid #45606c; border-radius: 999px; background: #182e39; color: #cfe0e7; font-size: 12px; }
            .title-manager-badge.yes { border-color: #3e7d59; background: #173526; color: #a9efc2; }
            .title-manager-note { margin: 16px 0 0; color: #9fb9c4; text-align: center; }
            @media (max-width: 800px) { .title-manager-tools { grid-template-columns: 1fr; } .title-manager-card p { min-height: 0; } }
            </style>

            <section class="title-manager-page">
                <div class="title-manager-heading">
                    <h1>Title Manager</h1>
                    <p>Add and maintain Guild Wars titles, ranks, and point requirements.</p>
                </div>

                <div class="title-manager-tools">
                    <section class="title-manager-card">
                        <h2>Add title</h2>
                        <p>Create a new account or character title.</p>
                        <form action="titlemanager.php" method="post">
                            <input type="hidden" name="title" value="addtitle">
                            <input class="title-manager-button" type="submit" value="Add title">
                        </form>
                    </section>

                    <section class="title-manager-card">
                        <h2>Modify title</h2>
                        <p>Edit an existing title and its settings.</p>
                        <form action="titlemanager.php" method="post">
                            <input type="hidden" name="title" value="modtitle">
                            <select name="tid" onchange="this.form.submit()">
                                <option selected disabled>Select title</option>
                                <?php include ('includes/title-select.php'); ?>
                            </select>
                            <noscript><input class="title-manager-button" type="submit" value="Modify title"></noscript>
                        </form>
                    </section>

                    <section class="title-manager-card">
                        <h2>Title ranks &amp; points</h2>
                        <p>Add or modify ranks and point requirements.</p>
                        <form action="titlemanager.php" method="post">
                            <input type="hidden" name="title" value="addsubtitle">
                            <select name="tid" onchange="this.form.submit()">
                                <option selected disabled>Select title</option>
                                <?php include ('includes/title-select.php'); ?>
                            </select>
                            <noscript><input class="title-manager-button" type="submit" value="Manage title ranks"></noscript>
                        </form>
                    </section>
                </div>

                <section class="title-manager-recent">
                    <div class="title-manager-recent-header">
                        <h2>Recently added titles</h2>
                        <p>The 15 newest title records in the database.</p>
                    </div>
                    <div class="title-manager-table-wrap">
                        <table class="title-manager-table">
                            <thead>
                                <tr>
                                    <th>ID</th>
                                    <th>Title</th>
                                    <th>Type ID</th>
                                    <th>Applies to</th>
                                    <th>Autofill ID</th>
                                    <th>Autofilled</th>
                                </tr>
                            </thead>
                            <tbody>
                            <?php
                            $stmtview = $con->prepare("SELECT * FROM gwtitles ORDER BY titlenameid DESC LIMIT 15");
                            $stmtview->execute();
                            $result = $stmtview->get_result();
                            while ($row = $result->fetch_assoc()) {
                                $tid = $row['titlenameid'];
                                $tname = $row['titlename'];
                                $ttype = $row['titletype'];
                                $tmr = $row['titlemaxrank'];
                                $taf = $row['autofilled'];

                                if ($ttype == "0") {
                                    $typeLabel = 'Account';
                                } else if ($ttype == "1") {
                                    $typeLabel = 'Character';
                                } else {
                                    echo '<tr><td colspan="6">Anything other than a 0 or 1 for title type means something broke!</td></tr>';
                                    break;
                                }

                                if ($taf == "0") {
                                    $autofillLabel = 'No';
                                    $autofillClass = '';
                                } else if ($taf == "1") {
                                    $autofillLabel = 'Yes';
                                    $autofillClass = ' yes';
                                } else {
                                    echo '<tr><td colspan="6">Anything other than a 0 or 1 for autofilled means something broke!</td></tr>';
                                    break;
                                }

                                echo '<tr>';
                                echo '<td>' . (int)$tid . '</td>';
                                echo '<td class="title-name">' . h($tname) . ' <span class="title-manager-badge">' . (int)$tmr . ' rank' . ((int)$tmr === 1 ? '' : 's') . '</span></td>';
                                echo '<td>' . h($ttype) . '</td>';
                                echo '<td>' . h($typeLabel) . '</td>';
                                echo '<td>' . h($taf) . '</td>';
                                echo '<td><span class="title-manager-badge' . $autofillClass . '">' . h($autofillLabel) . '</span></td>';
                                echo '</tr>';
                            }
                            $stmtview->close();
                            ?>
                            </tbody>
                        </table>
                    </div>
                </section>
                <p class="title-manager-note">If anything looks off, select the title above to fix it.</p>
            </section>
            <?php
        }
    }
}
include_once ('footer.php');
?>