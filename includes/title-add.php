<?php
if (isset($_SESSION['userid']) && isset($_SESSION['admin']) && (int)$_SESSION['admin'] === 1) {
?>
<style>
.title-form-page { width:min(100%,860px); margin:0 auto; padding:8px 0 28px; }
.title-form-heading { margin:4px 0 22px; text-align:center; }
.title-form-heading h1 { margin:0 0 7px; }
.title-form-heading p { margin:0; color:#9fb9c4; }
.title-form-card { padding:22px 24px; border:1px solid #2d6978; border-radius:8px; background:#122936; }
.title-form-grid { display:grid; grid-template-columns:minmax(280px,1.7fr) minmax(260px,1.25fr) minmax(140px,.65fr); gap:22px; align-items:start; }
.title-form-field label, .title-form-choice legend { display:block; margin:0 0 7px; color:#69dbe1; font-weight:700; }
.title-form-field small, .title-form-choice small { display:block; margin-top:6px; color:#9fb9c4; line-height:1.35; }
.title-form-field input[type="text"], .title-form-field input[type="number"] { width:100%; min-height:42px; padding:7px 10px; border:1px solid #547080; border-radius:5px; background:#edf2f5; color:#17242c; font:15px "Segoe UI",Tahoma,Arial,sans-serif; }
.title-form-choice { min-width:0; margin:0; padding:0; border:0; }
.title-scope-options { display:grid; grid-template-columns:1fr 1fr; gap:8px; }
.title-scope-option { display:flex; align-items:center; gap:7px; min-height:42px; margin:0; padding:8px 10px; border:1px solid #294b5b; border-radius:5px; background:#101f28; cursor:pointer; }
.title-scope-option input { margin:0; flex:0 0 auto; }
.title-scope-option span { font-weight:700; }
.title-form-flags { display:grid; grid-template-columns:1fr 1fr; gap:14px; margin-top:22px; }
.title-form-flag { padding:14px 16px; border:1px solid #294b5b; border-radius:6px; background:#101f28; }
.title-form-flag label { font-weight:700; }
.title-form-actions { display:flex; gap:12px; align-items:center; margin-top:22px; }
.title-form-actions button { min-height:42px; padding:8px 18px; border:1px solid #2999a5; border-radius:5px; background:#174454; color:#fff; font-weight:700; cursor:pointer; }
.title-form-actions a { color:#fff27a; font-weight:700; text-decoration:none; }
@media(max-width:760px){.title-form-grid,.title-form-flags{grid-template-columns:1fr;}.title-scope-options{grid-template-columns:1fr 1fr;}}
</style>
<section class="title-form-page">
<div class="title-form-heading"><h1>Add title</h1><p>Create the title record first. Rank names and point thresholds are managed separately after the title is created.</p></div>
<form class="title-form-card" action="titlemanager.php" method="post">
<?php echo csrf_input(); ?>
<div class="title-form-grid">
<div class="title-form-field"><label for="title-name">Title name</label><input id="title-name" type="text" name="titlename" maxlength="255" placeholder="Friend of the Kurzicks" required autofocus><small>The name shown throughout GWTTT.</small></div>
<fieldset class="title-form-choice"><legend>Applies to</legend><div class="title-scope-options"><label class="title-scope-option"><input type="radio" name="titletype" value="0" checked><span>Account</span></label><label class="title-scope-option"><input type="radio" name="titletype" value="1"><span>Character</span></label></div><small><strong>Account</strong> titles are shared by every character on that Guild Wars account. <strong>Character</strong> titles are tracked separately for each character.</small></fieldset>
<div class="title-form-field"><label for="title-max-rank">Max rank</label><input id="title-max-rank" type="number" name="titlemaxrank" min="0" max="15" required><small>Highest configured rank number (0–15).</small></div>
</div>
<div class="title-form-flags">
<div class="title-form-flag"><label><input type="checkbox" name="autofill" value="1"> Autofilled title</label><small>GWTTT calculates this title automatically instead of asking users to enter points.</small></div>
<div class="title-form-flag"><label><input type="checkbox" name="gwamm" value="1"> GWAMM aggregate title</label><small>Marks this as the special title whose progress is derived from completed qualifying titles. Only one title can hold this role.</small></div>
</div>
<input type="hidden" name="title" value="titlesubmit">
<div class="title-form-actions"><button type="submit">Create title</button><a href="titlemanager.php">Cancel</a></div>
</form>
</section>
<?php
}
?>