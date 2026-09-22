<?php
$pagetitle = 'Record Treasure Collection';
include_once('header.php');

if (empty($_SESSION['userid'])) {
    exit;
}

$userid = (int) $_SESSION['userid'];
$accid = isset($_SESSION['prefaccid']) ? (int) $_SESSION['prefaccid'] : 0;
$charid = isset($_SESSION['prefcharid']) ? (int) $_SESSION['prefcharid'] : 0;
$charname = isset($_SESSION['prefcharname']) ? (string) $_SESSION['prefcharname'] : '';
$locationId = isset($_GET['location']) ? (int) $_GET['location'] : (int) ($_POST['location_id'] ?? 0);
$error = '';
$success = '';

$characterValid = false;
if ($accid > 0 && $charid > 0) {
    $check = $con->prepare('SELECT 1 FROM gwchars WHERE charid = ? AND accid = ? AND userid = ? LIMIT 1');
    $check->bind_param('iii', $charid, $accid, $userid);
    $check->execute();
    $characterValid = (bool) $check->get_result()->fetch_row();
    $check->close();
}

$location = null;
if ($locationId > 0) {
    $stmt = $con->prepare('SELECT location_id, location_name, wiki_url, reset_days FROM gwtreasure_locations WHERE location_id = ? AND is_active = 1 LIMIT 1');
    $stmt->bind_param('i', $locationId);
    $stmt->execute();
    $location = $stmt->get_result()->fetch_assoc();
    $stmt->close();
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && $characterValid && $location) {
    $collectedOn = trim((string) ($_POST['collected_on'] ?? ''));
    $gold = filter_var($_POST['gold_received'] ?? null, FILTER_VALIDATE_INT, ['options' => ['min_range' => 0, 'max_range' => 16777215]]);
    $dropType = (string) ($_POST['drop_type'] ?? '');
    $dropDescription = trim((string) ($_POST['drop_description'] ?? ''));
    $notes = trim((string) ($_POST['notes'] ?? ''));
    $validTypes = ['weapon', 'material', 'rune_insignia', 'nothing'];

    $date = DateTime::createFromFormat('Y-m-d', $collectedOn);
    $validDate = $date && $date->format('Y-m-d') === $collectedOn;

    if (!$validDate) {
        $error = 'Please enter a valid collection date.';
    } elseif ($gold === false) {
        $error = 'Gold received must be zero or a positive whole number.';
    } elseif (!in_array($dropType, $validTypes, true)) {
        $error = 'Please choose a valid drop type.';
    } elseif ($dropType !== 'nothing' && $dropDescription === '') {
        $error = 'Please describe what dropped.';
    } elseif (mb_strlen($dropDescription) > 255 || mb_strlen($notes) > 255) {
        $error = 'Drop description and notes are limited to 255 characters each.';
    } else {
        if ($dropType === 'nothing') {
            $dropDescription = '';
        }
        $dropDescriptionDb = $dropDescription === '' ? null : $dropDescription;
        $notesDb = $notes === '' ? null : $notes;
        $insert = $con->prepare(
            'INSERT INTO gwtreasure_history '
            . '(userid, accid, charid, location_id, collected_on, gold_received, drop_type, drop_description, notes) '
            . 'VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)'
        );
        $insert->bind_param('iiiisisss', $userid, $accid, $charid, $locationId, $collectedOn, $gold, $dropType, $dropDescriptionDb, $notesDb);
        if ($insert->execute()) {
            $success = 'Collection recorded for ' . $location['location_name'] . '.';
        } else {
            error_log('GWTTT treasure insert failed: ' . $insert->error);
            $error = 'The collection could not be saved. Please try again.';
        }
        $insert->close();
    }
}
?>
<section class="treasure-page">
    <div class="treasure-heading">
        <h1>Record Treasure Collection</h1>
        <?php if ($characterValid && $location): ?>
            <p><strong><?php echo h($charname); ?></strong> at
            <?php if (!empty($location['wiki_url'])): ?>
                <a class="treasure-wiki-link" href="<?php echo h($location['wiki_url']); ?>" target="_blank" rel="noopener noreferrer"><?php echo h($location['location_name']); ?></a>
            <?php else: ?>
                <?php echo h($location['location_name']); ?>
            <?php endif; ?>
            </p>
        <?php endif; ?>
    </div>

    <?php if (!$characterValid): ?>
        <p class="treasure-form-message treasure-error">Select an account and character in the header first.</p>
    <?php elseif (!$location): ?>
        <p class="treasure-form-message treasure-error">That treasure location could not be found.</p>
    <?php else: ?>
        <?php if ($error !== ''): ?><p class="treasure-form-message treasure-error"><?php echo h($error); ?></p><?php endif; ?>
        <?php if ($success !== ''): ?>
            <p class="treasure-form-message treasure-success"><?php echo h($success); ?></p>
            <div class="treasure-form-actions"><a class="treasure-button" href="treasures.php">Back to Treasure Tracker</a></div>
        <?php else: ?>
        <form class="treasure-record-form" method="post" action="treasure-record.php?location=<?php echo (int) $locationId; ?>">
            <?php echo csrf_input(); ?>
            <input type="hidden" name="location_id" value="<?php echo (int) $locationId; ?>">

            <div class="treasure-form-row">
                <label for="collected_on">Collection date</label>
                <input id="collected_on" name="collected_on" type="date" value="<?php echo h($_POST['collected_on'] ?? date('Y-m-d')); ?>" required>
            </div>
            <div class="treasure-form-row">
                <label for="gold_received">Gold received</label>
                <input id="gold_received" name="gold_received" type="number" min="0" max="16777215" value="<?php echo h($_POST['gold_received'] ?? '0'); ?>" required>
            </div>
            <div class="treasure-form-row">
                <label for="drop_type">What dropped?</label>
                <select id="drop_type" name="drop_type" required>
                    <?php $selectedType = $_POST['drop_type'] ?? 'weapon'; ?>
                    <option value="weapon"<?php echo $selectedType === 'weapon' ? ' selected' : ''; ?>>Weapon</option>
                    <option value="material"<?php echo $selectedType === 'material' ? ' selected' : ''; ?>>Rare Material</option>
                    <option value="rune_insignia"<?php echo $selectedType === 'rune_insignia' ? ' selected' : ''; ?>>Rune / Insignia</option>
                    <option value="nothing"<?php echo $selectedType === 'nothing' ? ' selected' : ''; ?>>Nothing!</option>
                </select>
            </div>
            <div class="treasure-form-row" id="drop-description-row">
                <label for="drop_description">Drop description</label>
                <input id="drop_description" name="drop_description" type="text" maxlength="255" value="<?php echo h($_POST['drop_description'] ?? ''); ?>" placeholder="e.g. Gold req 9 sword, Ruby, Superior Vigor">
            </div>
            <div class="treasure-form-row">
                <label for="notes">Notes</label>
                <input id="notes" name="notes" type="text" maxlength="255" value="<?php echo h($_POST['notes'] ?? ''); ?>" placeholder="Optional">
            </div>
            <div class="treasure-form-actions">
                <button type="submit">Record collection</button>
                <a class="treasure-button treasure-button-secondary" href="treasures.php">Cancel</a>
            </div>
        </form>
        <script>
        (function () {
            const type = document.getElementById('drop_type');
            const row = document.getElementById('drop-description-row');
            const input = document.getElementById('drop_description');
            function refreshDropDescription() {
                const hidden = type.value === 'nothing';
                row.hidden = hidden;
                input.required = !hidden;
                if (hidden) input.value = '';
            }
            type.addEventListener('change', refreshDropDescription);
            refreshDropDescription();
        }());
        </script>
        <?php endif; ?>
    <?php endif; ?>
</section>
<?php include_once('footer.php'); ?>
