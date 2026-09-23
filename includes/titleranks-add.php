<?php
if (isset($_SESSION['userid']) && isset($_SESSION['admin']) && (int)$_SESSION['admin'] === 1) {
    unset($_SESSION['title']);

    $titleId = isset($_SESSION['tid']) ? (int)$_SESSION['tid'] : filter_var($_POST['tid'] ?? null, FILTER_VALIDATE_INT);
    if ($titleId === false || $titleId < 1) {
        http_response_code(400);
        echo 'Invalid title selected.<br /><br />';
        echo 'Return to <a href="titlemanager.php" class="navlink">title manager</a>';
        return;
    }

    $stmtname = $con->prepare('SELECT titlename, titlemaxrank FROM gwtitles WHERE titlenameid = ?');
    $stmtname->bind_param('i', $titleId);
    $stmtname->execute();
    $titleResult = $stmtname->get_result();
    $titleRow = $titleResult->fetch_assoc();
    $stmtname->close();

    if (!$titleRow) {
        http_response_code(404);
        echo 'Title not found.<br /><br />';
        echo 'Return to <a href="titlemanager.php" class="navlink">title manager</a>';
        return;
    }

    $trank = $con->prepare('SELECT COALESCE(MAX(strank), 0) AS current_rank FROM gwsubtitles WHERE titlenameid = ?');
    $trank->bind_param('i', $titleId);
    $trank->execute();
    $rankRow = $trank->get_result()->fetch_assoc();
    $trank->close();
    $tr = ((int)$rankRow['current_rank']) + 1;
    $maxRank = (int)$titleRow['titlemaxrank'];
?>
<style>
.rank-admin-page { width:min(100%,900px); margin:0 auto; padding:8px 0 30px; }
.rank-admin-heading { margin-bottom:20px; text-align:center; }
.rank-admin-heading h1 { margin:0 0 6px; }
.rank-admin-heading p { margin:4px 0; color:#9fb9c4; }
.rank-admin-card { margin:0 0 22px; padding:20px 22px; border:1px solid #2d6978; border-radius:8px; background:#122936; }
.rank-admin-card h2 { margin:0 0 8px; color:#69dbe1; }
.rank-admin-card > p { margin:0 0 16px; color:#b9cbd3; }
.rank-add-grid { display:grid; grid-template-columns:minmax(260px,1.5fr) minmax(180px,1fr) 120px; gap:12px; align-items:end; }
.rank-field label { display:block; margin-bottom:6px; color:#9fd8e5; font-weight:700; }
.rank-field small { display:block; margin-top:5px; color:#9fb9c4; line-height:1.3; }
.rank-field input { width:100%; min-height:42px; box-sizing:border-box; padding:7px 10px; border:1px solid #547080; border-radius:5px; background:#edf2f5; color:#17242c; font:15px "Segoe UI",Tahoma,Arial,sans-serif; }
.rank-actions { margin-top:18px; display:flex; gap:12px; align-items:center; }
.rank-actions input[type="submit"], .rank-actions button { min-height:42px; padding:8px 18px; border:1px solid #2999a5; border-radius:5px; background:#174454; color:#fff; font-weight:700; cursor:pointer; }
.rank-actions a { color:#fff27a; font-weight:700; text-decoration:none; }
.rank-table { width:100%; border-collapse:collapse; }
.rank-table th, .rank-table td { padding:9px 10px; border:1px solid #31515e; text-align:left; }
.rank-table th { background:#193b4b; color:#fff; }
.rank-table td.num, .rank-table th.num { text-align:center; }
.rank-table td.points { text-align:right; }
.rank-empty { padding:14px 0 2px; color:#9fb9c4; }
@media(max-width:700px){.rank-add-grid{grid-template-columns:1fr}.rank-actions{align-items:stretch;flex-direction:column}.rank-actions input[type="submit"],.rank-actions a{width:100%;box-sizing:border-box;text-align:center}}
</style>
<section class="rank-admin-page">
<div class="rank-admin-heading"><h1>Manage title ranks</h1><p>Title: <strong><?php echo h($titleRow['titlename']); ?></strong></p><p>Configured maximum rank: <strong><?php echo $maxRank; ?></strong></p></div>
<?php if ($tr > $maxRank) { ?>
<div class="rank-admin-card"><h2>All ranks configured</h2><p>This title already has ranks 1 through <?php echo $maxRank; ?>. Edit an existing rank below if a name or point threshold needs correction.</p></div>
<?php } else { ?>
<div class="rank-admin-card">
<h2>Add rank <?php echo $tr; ?> of <?php echo $maxRank; ?></h2>
<p>Add ranks in order. <strong>Title points</strong> is the cumulative point total required by the game to earn this rank, not the number of points between ranks.</p>
<form action="titlemanager.php" method="post">
<?php echo csrf_input(); ?>
<div class="rank-add-grid">
<div class="rank-field"><label for="rank-name">Rank name</label><input id="rank-name" type="text" name="titlerankname" maxlength="255" required autofocus><small>The in-game name displayed after this threshold is reached.</small></div>
<div class="rank-field"><label for="rank-points">Point threshold</label><input id="rank-points" type="number" name="titlepoints" min="0" step="1" required><small>Total points required to earn rank <?php echo $tr; ?>.</small></div>
<div class="rank-field"><label for="rank-level">Rank level</label><input id="rank-level" type="number" readonly name="titlerank" min="1" max="<?php echo $maxRank; ?>" value="<?php echo $tr; ?>"><small>Assigned automatically.</small></div>
</div>
<input type="hidden" name="title" value="titleranksubmit"><input type="hidden" name="titlenameid" value="<?php echo $titleId; ?>">
<div class="rank-actions"><input type="submit" value="Add rank <?php echo $tr; ?>"></div>
</form>
</div>
<?php } ?>
<div class="rank-admin-card"><h2>Configured ranks</h2><p>Select one or more ranks to correct their name, point threshold, or rank level.</p>
<form action="titlemanager.php" method="post"><?php echo csrf_input(); ?>
<table class="rank-table"><tr><th class="num">Rank</th><th>Rank name</th><th>Point threshold</th><th class="num">Edit</th></tr>
<?php
    $stmtview = $con->prepare('SELECT stnameid, titlenameid, stname, stpoints, strank FROM gwsubtitles WHERE titlenameid = ? ORDER BY strank ASC');
    $stmtview->bind_param('i', $titleId);
    $stmtview->execute();
    $result = $stmtview->get_result();
    $rankCount = 0;
    while ($row = $result->fetch_assoc()) {
        $rankCount++;
        echo '<tr><td class="num">' . (int)$row['strank'] . '</td><td>' . h($row['stname']) . '</td><td class="points">' . number_format((int)$row['stpoints']) . '</td><td class="num"><input type="checkbox" name="editstitle[]" value="' . (int)$row['stnameid'] . '" aria-label="Edit rank ' . (int)$row['strank'] . '"></td></tr>';
    }
    $stmtview->close();
    if ($rankCount === 0) {
        echo '<tr><td colspan="4" class="rank-empty">No ranks have been configured for this title yet.</td></tr>';
    }
    $_SESSION['tid'] = $titleId;
?>
</table>
<input type="hidden" name="title" value="modsubtitle">
<div class="rank-actions"><input type="submit" value="Edit selected ranks"><a href="titlemanager.php">Return to Title Manager</a></div>
</form></div>
</section>
<?php
}
?>