<?php
$pagetitle = 'Record Treasure Collection';
include_once('header.php');

if (empty($_SESSION['userid'])) { exit; }

$userid = (int) $_SESSION['userid'];
$accid = (int) ($_SESSION['prefaccid'] ?? 0);
$charid = (int) ($_SESSION['prefcharid'] ?? 0);
$charname = (string) ($_SESSION['prefcharname'] ?? '');
$locationId = isset($_GET['location']) ? (int) $_GET['location'] : (int) ($_POST['location_id'] ?? 0);
$error = '';

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
    $stmt->bind_param('i', $locationId); $stmt->execute();
    $location = $stmt->get_result()->fetch_assoc(); $stmt->close();
}

function treasure_lookup_rows(mysqli $con, string $sql): array {
    $rows = []; $r = $con->query($sql);
    while ($row = $r->fetch_assoc()) { $rows[] = $row; }
    $r->close(); return $rows;
}
function treasure_lookup_exists(mysqli $con, string $table, string $column, int $value): bool {
    $allowed = [
        'gwtreasure_rarities' => 'rarity_id', 'gwtreasure_requirements' => 'requirement',
        'gwtreasure_weapon_types' => 'weapon_type_id', 'gwtreasure_attributes' => 'attribute_id',
        'gwtreasure_materials' => 'material_id', 'gwtreasure_runes' => 'rune_id',
        'gwtreasure_insignias' => 'insignia_id'
    ];
    if (($allowed[$table] ?? '') !== $column) { return false; }
    $q = $con->prepare("SELECT 1 FROM {$table} WHERE {$column} = ? LIMIT 1");
    $q->bind_param('i', $value); $q->execute();
    $exists = (bool) $q->get_result()->fetch_row(); $q->close(); return $exists;
}

$rarities = treasure_lookup_rows($con, 'SELECT rarity_id, rarity_name FROM gwtreasure_rarities ORDER BY rarity_id');
$requirements = treasure_lookup_rows($con, 'SELECT requirement FROM gwtreasure_requirements ORDER BY requirement');
$weaponTypes = treasure_lookup_rows($con, 'SELECT weapon_type_id, weapon_type_name FROM gwtreasure_weapon_types ORDER BY weapon_type_id');
$materials = treasure_lookup_rows($con, 'SELECT material_id, material_name FROM gwtreasure_materials ORDER BY material_name');
$runes = treasure_lookup_rows($con, 'SELECT r.rune_id, r.rune_name, p.profession_name FROM gwtreasure_runes r JOIN gwtreasure_professions p ON p.profession_id=r.profession_id ORDER BY r.profession_id, r.rune_name');
$insignias = treasure_lookup_rows($con, 'SELECT i.insignia_id, i.insignia_name, p.profession_name FROM gwtreasure_insignias i JOIN gwtreasure_professions p ON p.profession_id=i.profession_id ORDER BY i.profession_id, i.insignia_name');
$weaponAttributeMap = [];
$map = $con->query('SELECT m.weapon_type_id, a.attribute_id, a.attribute_name FROM gwtreasure_weapon_attribute_map m JOIN gwtreasure_attributes a ON a.attribute_id=m.attribute_id ORDER BY m.weapon_type_id, a.attribute_name');
while ($row = $map->fetch_assoc()) { $weaponAttributeMap[(int)$row['weapon_type_id']][] = ['id'=>(int)$row['attribute_id'], 'name'=>$row['attribute_name']]; }
$map->close();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!$characterValid) { $error = 'The selected character is no longer valid for this account. Please select the character again.'; }
    elseif (!$location) { $error = 'That treasure location could not be found.'; }
    else {
        $collectedOn = trim((string)($_POST['collected_on'] ?? ''));
        $gold = filter_var($_POST['gold_received'] ?? null, FILTER_VALIDATE_INT, ['options'=>['min_range'=>0,'max_range'=>16777215]]);
        $dropType = (string)($_POST['drop_type'] ?? '');
        $notes = trim((string)($_POST['notes'] ?? ''));
        $validTypes = ['weapon','material','rune_insignia','nothing'];
        $date = DateTime::createFromFormat('!Y-m-d', $collectedOn);
        $validDate = $date && $date->format('Y-m-d') === $collectedOn;
        $today = new DateTime('today');

        $rarityId=$requirement=$weaponTypeId=$attributeId=$materialId=$runeId=$insigniaId=null;
        $itemName=null; $dropDescription=null;

        if (!$validDate) { $error = 'Please enter a valid collection date.'; }
        elseif ($date > $today) { $error = 'Collection date cannot be in the future.'; }
        elseif ($gold === false) { $error = 'Gold received must be zero or a positive whole number.'; }
        elseif (!in_array($dropType,$validTypes,true)) { $error = 'Please choose a valid drop type.'; }
        elseif (mb_strlen($notes) > 255) { $error = 'Notes are limited to 255 characters.'; }
        elseif ($dropType === 'weapon') {
            $rarityId=(int)($_POST['rarity_id']??0); $requirement=(int)($_POST['requirement']??-1);
            $weaponTypeId=(int)($_POST['weapon_type_id']??0); $attributeId=(int)($_POST['attribute_id']??0);
            $itemName=trim((string)($_POST['item_name']??''));
            if (!treasure_lookup_exists($con,'gwtreasure_rarities','rarity_id',$rarityId) || !treasure_lookup_exists($con,'gwtreasure_requirements','requirement',$requirement) || !treasure_lookup_exists($con,'gwtreasure_weapon_types','weapon_type_id',$weaponTypeId) || !treasure_lookup_exists($con,'gwtreasure_attributes','attribute_id',$attributeId)) {
                $error='Please choose valid weapon details.';
            } elseif (mb_strlen($itemName)>150) { $error='Item name is limited to 150 characters.'; }
            else {
                $pair=$con->prepare('SELECT 1 FROM gwtreasure_weapon_attribute_map WHERE weapon_type_id=? AND attribute_id=? LIMIT 1');
                $pair->bind_param('ii',$weaponTypeId,$attributeId); $pair->execute(); $validPair=(bool)$pair->get_result()->fetch_row(); $pair->close();
                if (!$validPair) { $error='That attribute is not valid for the selected weapon type.'; }
            }
            if ($itemName==='') { $itemName=null; }
        } elseif ($dropType === 'material') {
            $materialId=(int)($_POST['material_id']??0);
            if (!treasure_lookup_exists($con,'gwtreasure_materials','material_id',$materialId)) { $error='Please choose a valid rare material.'; }
        } elseif ($dropType === 'rune_insignia') {
            $rarityId=(int)($_POST['rune_rarity_id']??0); $runeRaw=(int)($_POST['rune_id']??0); $insigniaRaw=(int)($_POST['insignia_id']??0);
            $validRarity=in_array($rarityId,[2,3,4],true);
            $validRune=$runeRaw===0 || treasure_lookup_exists($con,'gwtreasure_runes','rune_id',$runeRaw);
            $validInsignia=$insigniaRaw===0 || treasure_lookup_exists($con,'gwtreasure_insignias','insignia_id',$insigniaRaw);
            if (!$validRarity || !$validRune || !$validInsignia || ($runeRaw===0 && $insigniaRaw===0)) { $error='Choose a valid rarity and at least one rune or insignia.'; }
            else { $runeId=$runeRaw?:null; $insigniaId=$insigniaRaw?:null; }
        }

        if ($error === '') {
            $notesDb=$notes===''?null:$notes;
            $insert=$con->prepare('INSERT INTO gwtreasure_history (userid,accid,charid,location_id,collected_on,gold_received,drop_type,drop_description,rarity_id,requirement,weapon_type_id,attribute_id,item_name,material_id,rune_id,insignia_id,notes) VALUES (?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?)');
            $insert->bind_param('iiiisissiiiisiiis',$userid,$accid,$charid,$locationId,$collectedOn,$gold,$dropType,$dropDescription,$rarityId,$requirement,$weaponTypeId,$attributeId,$itemName,$materialId,$runeId,$insigniaId,$notesDb);
            if ($insert->execute()) {
                $_SESSION['treasure_message']='Collection recorded for '.$location['location_name'].' on '.date('M j, Y',strtotime($collectedOn)).'.';
                $insert->close(); header('Location: treasures.php'); exit;
            }
            error_log('GWTTT treasure insert failed: '.$insert->error); $insert->close();
            $error='The collection could not be saved. Please verify the selected account and character and try again.';
        }
    }
}
function selected_value($posted, $value): string { return (string)$posted === (string)$value ? ' selected' : ''; }
?>
<section class="treasure-page"><div class="treasure-heading"><h1>Record Treasure Collection</h1>
<?php if ($characterValid && $location): ?><p><strong><?php echo h($charname); ?></strong> at <?php if (!empty($location['wiki_url'])): ?><a class="treasure-wiki-link" href="<?php echo h($location['wiki_url']); ?>" target="_blank" rel="noopener noreferrer"><?php echo h($location['location_name']); ?></a><?php else: echo h($location['location_name']); endif; ?></p><?php endif; ?></div>
<?php if (!$characterValid): ?><p class="treasure-form-message treasure-error">Select an account and character in the header first.</p>
<?php elseif (!$location): ?><p class="treasure-form-message treasure-error">That treasure location could not be found.</p>
<?php else: ?>
<?php if ($error!==''): ?><p class="treasure-form-message treasure-error"><?php echo h($error); ?></p><?php endif; ?>
<form class="treasure-record-form" method="post" action="treasure-record.php?location=<?php echo (int)$locationId; ?>">
<?php echo csrf_input(); ?><input type="hidden" name="location_id" value="<?php echo (int)$locationId; ?>">
<div class="treasure-form-row"><label for="collected_on">Collection date</label><input id="collected_on" name="collected_on" type="date" max="<?php echo date('Y-m-d'); ?>" value="<?php echo h($_POST['collected_on']??date('Y-m-d')); ?>" required></div>
<div class="treasure-form-row"><label for="gold_received">Gold received</label><input id="gold_received" name="gold_received" type="number" min="0" max="16777215" value="<?php echo h($_POST['gold_received']??'0'); ?>" required></div>
<div class="treasure-form-row"><label for="drop_type">What dropped?</label><select id="drop_type" name="drop_type" required><?php $selectedType=$_POST['drop_type']??'weapon'; ?><option value="weapon"<?php echo selected_value($selectedType,'weapon'); ?>>Weapon</option><option value="material"<?php echo selected_value($selectedType,'material'); ?>>Rare Material</option><option value="rune_insignia"<?php echo selected_value($selectedType,'rune_insignia'); ?>>Rune / Insignia</option><option value="nothing"<?php echo selected_value($selectedType,'nothing'); ?>>Nothing!</option></select></div>

<div class="treasure-drop-fields" data-drop-fields="weapon">
<div class="treasure-form-row"><label for="rarity_id">Rarity</label><select id="rarity_id" name="rarity_id"><?php foreach($rarities as $x): ?><option value="<?php echo (int)$x['rarity_id']; ?>"<?php echo selected_value($_POST['rarity_id']??4,$x['rarity_id']); ?>><?php echo h($x['rarity_name']); ?></option><?php endforeach; ?></select></div>
<div class="treasure-form-row"><label for="requirement">Requirement</label><select id="requirement" name="requirement"><?php foreach($requirements as $x): ?><option value="<?php echo (int)$x['requirement']; ?>"<?php echo selected_value($_POST['requirement']??9,$x['requirement']); ?>><?php echo (int)$x['requirement']; ?></option><?php endforeach; ?></select></div>
<div class="treasure-form-row"><label for="weapon_type_id">Weapon type</label><select id="weapon_type_id" name="weapon_type_id"><option value="">Choose item type</option><?php foreach($weaponTypes as $x): ?><option value="<?php echo (int)$x['weapon_type_id']; ?>"<?php echo selected_value($_POST['weapon_type_id']??'',$x['weapon_type_id']); ?>><?php echo h($x['weapon_type_name']); ?></option><?php endforeach; ?></select></div>
<div class="treasure-form-row"><label for="attribute_id">Attribute</label><select id="attribute_id" name="attribute_id"><option value="" disabled selected>Choose weapon type first</option></select></div>
<div class="treasure-form-row"><label for="item_name">Item name</label><input id="item_name" name="item_name" type="text" maxlength="150" value="<?php echo h($_POST['item_name']??''); ?>" placeholder="Optional weapon name"></div>
</div>

<div class="treasure-drop-fields" data-drop-fields="material" hidden><div class="treasure-form-row"><label for="material_id">Rare material</label><select id="material_id" name="material_id"><option value="">Choose material</option><?php foreach($materials as $x): ?><option value="<?php echo (int)$x['material_id']; ?>"<?php echo selected_value($_POST['material_id']??'',$x['material_id']); ?>><?php echo h($x['material_name']); ?></option><?php endforeach; ?></select></div></div>

<div class="treasure-drop-fields" data-drop-fields="rune_insignia" hidden>
<div class="treasure-form-row"><label for="rune_rarity_id">Rarity</label><select id="rune_rarity_id" name="rune_rarity_id"><option value="2"<?php echo selected_value($_POST['rune_rarity_id']??4,2); ?>>Blue</option><option value="3"<?php echo selected_value($_POST['rune_rarity_id']??4,3); ?>>Purple</option><option value="4"<?php echo selected_value($_POST['rune_rarity_id']??4,4); ?>>Gold</option></select></div>
<div class="treasure-form-row"><label for="rune_id">Rune</label><select id="rune_id" name="rune_id"><option value="0">None</option><?php foreach($runes as $x): ?><option value="<?php echo (int)$x['rune_id']; ?>"<?php echo selected_value($_POST['rune_id']??0,$x['rune_id']); ?>><?php echo h(($x['profession_name']==='None'?'General':$x['profession_name']).' — '.$x['rune_name']); ?></option><?php endforeach; ?></select></div>
<div class="treasure-form-row"><label for="insignia_id">Insignia</label><select id="insignia_id" name="insignia_id"><option value="0">None</option><?php foreach($insignias as $x): ?><option value="<?php echo (int)$x['insignia_id']; ?>"<?php echo selected_value($_POST['insignia_id']??0,$x['insignia_id']); ?>><?php echo h(($x['profession_name']==='None'?'General':$x['profession_name']).' — '.$x['insignia_name']); ?></option><?php endforeach; ?></select></div>
</div>

<div class="treasure-form-row"><label for="notes">Notes</label><input id="notes" name="notes" type="text" maxlength="255" value="<?php echo h($_POST['notes']??''); ?>" placeholder="Optional"></div>
<div class="treasure-form-actions"><button type="submit">Record collection</button><a class="treasure-button treasure-button-secondary" href="treasures.php">Cancel</a></div></form>
<script>
(function(){
const dropType=document.getElementById('drop_type');
const groups=document.querySelectorAll('[data-drop-fields]');
const weaponType=document.getElementById('weapon_type_id');
const attribute=document.getElementById('attribute_id');
const attributeMap=<?php echo json_encode($weaponAttributeMap,JSON_HEX_TAG|JSON_HEX_AMP|JSON_HEX_APOS|JSON_HEX_QUOT); ?>;
const postedAttribute=<?php echo json_encode((string)($_POST['attribute_id']??'')); ?>;
function refreshGroups(){groups.forEach(function(g){const active=g.dataset.dropFields===dropType.value;g.hidden=!active;g.querySelectorAll('select,input').forEach(function(el){el.disabled=!active;});});}
function refreshAttributes(){
    const attrs=attributeMap[weaponType.value]||[];
    const placeholder=new Option(attrs.length?'Choose attribute':'Choose weapon type first','');
    placeholder.disabled=true;
    placeholder.selected=true;
    attribute.replaceChildren(placeholder);
    attrs.forEach(function(a){attribute.add(new Option(a.name,String(a.id)));});
    if(postedAttribute&&attrs.some(a=>String(a.id)===postedAttribute))attribute.value=postedAttribute;
    else if(attrs.length===1)attribute.value=String(attrs[0].id);
    attribute.disabled=dropType.value!=='weapon'||attrs.length===0;
}
dropType.addEventListener('change',function(){refreshGroups();refreshAttributes();});weaponType.addEventListener('change',refreshAttributes);refreshGroups();refreshAttributes();
}());
</script>
<?php endif; ?></section>
<?php include_once('footer.php'); ?>