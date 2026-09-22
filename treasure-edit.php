<?php
$pagetitle = 'Edit Treasure Collection';
include_once('header.php');
if (empty($_SESSION['userid'])) { exit; }

$userid=(int)$_SESSION['userid'];
$historyId=(int)($_GET['id'] ?? $_POST['history_id'] ?? 0);
$error='';

function te_rows(mysqli $con,string $sql):array{$rows=[];$r=$con->query($sql);while($x=$r->fetch_assoc())$rows[]=$x;$r->close();return $rows;}
function te_exists(mysqli $con,string $table,string $column,int $value):bool{
    $allowed=['gwtreasure_rarities'=>'rarity_id','gwtreasure_requirements'=>'requirement','gwtreasure_weapon_types'=>'weapon_type_id','gwtreasure_attributes'=>'attribute_id','gwtreasure_materials'=>'material_id','gwtreasure_runes'=>'rune_id','gwtreasure_insignias'=>'insignia_id','gwtreasure_locations'=>'location_id'];
    if(($allowed[$table]??'')!==$column)return false;
    $q=$con->prepare("SELECT 1 FROM {$table} WHERE {$column}=? LIMIT 1");$q->bind_param('i',$value);$q->execute();$ok=(bool)$q->get_result()->fetch_row();$q->close();return $ok;
}
function te_selected($a,$b):string{return (string)$a===(string)$b?' selected':'';}

$entry=null;
if($historyId>0){
    $q=$con->prepare('SELECT h.*,l.location_name,l.wiki_url,c.charname FROM gwtreasure_history h JOIN gwtreasure_locations l ON l.location_id=h.location_id JOIN gwchars c ON c.charid=h.charid AND c.userid=h.userid WHERE h.treasure_history_id=? AND h.userid=? LIMIT 1');
    $q->bind_param('ii',$historyId,$userid);$q->execute();$entry=$q->get_result()->fetch_assoc();$q->close();
}

if(!$entry){http_response_code(404);?>
<section class="treasure-page"><div class="treasure-heading"><h1>Treasure Collection Not Found</h1><p>That collection does not exist or does not belong to your account.</p></div><div class="treasure-form-actions"><a class="treasure-button treasure-button-secondary" href="treasure-history.php">Back to Treasure History</a></div></section>
<?php include_once('footer.php');exit;}

$rarities=te_rows($con,'SELECT rarity_id,rarity_name FROM gwtreasure_rarities ORDER BY rarity_id');
$requirements=te_rows($con,'SELECT requirement FROM gwtreasure_requirements ORDER BY requirement');
$weaponTypes=te_rows($con,'SELECT weapon_type_id,weapon_type_name FROM gwtreasure_weapon_types ORDER BY weapon_type_id');
$materials=te_rows($con,'SELECT material_id,material_name FROM gwtreasure_materials ORDER BY material_name');
$runes=te_rows($con,'SELECT r.rune_id,r.rune_name,p.profession_name FROM gwtreasure_runes r JOIN gwtreasure_professions p ON p.profession_id=r.profession_id ORDER BY r.profession_id,r.rune_name');
$insignias=te_rows($con,'SELECT i.insignia_id,i.insignia_name,p.profession_name FROM gwtreasure_insignias i JOIN gwtreasure_professions p ON p.profession_id=i.profession_id ORDER BY i.profession_id,i.insignia_name');
$locations=te_rows($con,'SELECT location_id,location_name FROM gwtreasure_locations ORDER BY location_name');
$attributeMap=[];$r=$con->query('SELECT m.weapon_type_id,a.attribute_id,a.attribute_name FROM gwtreasure_weapon_attribute_map m JOIN gwtreasure_attributes a ON a.attribute_id=m.attribute_id ORDER BY m.weapon_type_id,a.attribute_name');while($x=$r->fetch_assoc())$attributeMap[(int)$x['weapon_type_id']][]=['id'=>(int)$x['attribute_id'],'name'=>$x['attribute_name']];$r->close();

if($_SERVER['REQUEST_METHOD']==='POST'){
    $action=(string)($_POST['action']??'save');
    if($action==='delete'){
        $del=$con->prepare('DELETE FROM gwtreasure_history WHERE treasure_history_id=? AND userid=?');$del->bind_param('ii',$historyId,$userid);$del->execute();$deleted=$del->affected_rows;$del->close();
        if($deleted===1){$_SESSION['treasure_message']='Treasure collection deleted.';header('Location: treasure-history.php');exit;}
        $error='The collection could not be deleted.';
    } else {
        $locationId=(int)($_POST['location_id']??0);$collectedOn=trim((string)($_POST['collected_on']??''));
        $gold=filter_var($_POST['gold_received']??null,FILTER_VALIDATE_INT,['options'=>['min_range'=>0,'max_range'=>16777215]]);
        $dropType=(string)($_POST['drop_type']??'');$notes=trim((string)($_POST['notes']??''));
        $date=DateTime::createFromFormat('!Y-m-d',$collectedOn);$validDate=$date&&$date->format('Y-m-d')===$collectedOn;$today=new DateTime('today');
        $rarityId=$requirement=$weaponTypeId=$attributeId=$materialId=$runeId=$insigniaId=null;$itemName=null;$dropDescription=null;
        if(!te_exists($con,'gwtreasure_locations','location_id',$locationId))$error='Please choose a valid treasure location.';
        elseif(!$validDate)$error='Please enter a valid collection date.';
        elseif($date>$today)$error='Collection date cannot be in the future.';
        elseif($gold===false)$error='Gold received must be zero or a positive whole number.';
        elseif(!in_array($dropType,['weapon','material','rune_insignia','nothing'],true))$error='Please choose a valid drop type.';
        elseif(mb_strlen($notes)>255)$error='Notes are limited to 255 characters.';
        elseif($dropType==='weapon'){
            $rarityId=(int)($_POST['rarity_id']??0);$requirement=(int)($_POST['requirement']??-1);$weaponTypeId=(int)($_POST['weapon_type_id']??0);$attributeId=(int)($_POST['attribute_id']??0);$itemName=trim((string)($_POST['item_name']??''));
            if(!te_exists($con,'gwtreasure_rarities','rarity_id',$rarityId)||!te_exists($con,'gwtreasure_requirements','requirement',$requirement)||!te_exists($con,'gwtreasure_weapon_types','weapon_type_id',$weaponTypeId)||!te_exists($con,'gwtreasure_attributes','attribute_id',$attributeId))$error='Please choose valid weapon details.';
            elseif(mb_strlen($itemName)>150)$error='Item name is limited to 150 characters.';
            else{$pair=$con->prepare('SELECT 1 FROM gwtreasure_weapon_attribute_map WHERE weapon_type_id=? AND attribute_id=? LIMIT 1');$pair->bind_param('ii',$weaponTypeId,$attributeId);$pair->execute();$ok=(bool)$pair->get_result()->fetch_row();$pair->close();if(!$ok)$error='That attribute is not valid for the selected weapon type.';}
            if($itemName==='')$itemName=null;
        }elseif($dropType==='material'){
            $materialId=(int)($_POST['material_id']??0);if(!te_exists($con,'gwtreasure_materials','material_id',$materialId))$error='Please choose a valid rare material.';
        }elseif($dropType==='rune_insignia'){
            $rarityId=(int)($_POST['rune_rarity_id']??0);$rr=(int)($_POST['rune_id']??0);$ir=(int)($_POST['insignia_id']??0);
            if(!in_array($rarityId,[2,3,4],true)||($rr!==0&&!te_exists($con,'gwtreasure_runes','rune_id',$rr))||($ir!==0&&!te_exists($con,'gwtreasure_insignias','insignia_id',$ir))||($rr===0&&$ir===0))$error='Choose a valid rarity and at least one rune or insignia.';else{$runeId=$rr?:null;$insigniaId=$ir?:null;}
        }
        if($error===''){
            $notesDb=$notes===''?null:$notes;
            $u=$con->prepare('UPDATE gwtreasure_history SET location_id=?,collected_on=?,gold_received=?,drop_type=?,drop_description=?,rarity_id=?,requirement=?,weapon_type_id=?,attribute_id=?,item_name=?,material_id=?,rune_id=?,insignia_id=?,notes=? WHERE treasure_history_id=? AND userid=?');
            $u->bind_param('isissiiiisiiisii',$locationId,$collectedOn,$gold,$dropType,$dropDescription,$rarityId,$requirement,$weaponTypeId,$attributeId,$itemName,$materialId,$runeId,$insigniaId,$notesDb,$historyId,$userid);
            if($u->execute()){$_SESSION['treasure_message']='Treasure collection updated.';$u->close();header('Location: treasure-history.php');exit;}error_log('GWTTT treasure update failed: '.$u->error);$u->close();$error='The collection could not be updated.';
        }
        foreach(['location_id'=>$locationId,'collected_on'=>$collectedOn,'gold_received'=>$gold===false?($_POST['gold_received']??''):$gold,'drop_type'=>$dropType,'rarity_id'=>$rarityId,'requirement'=>$requirement,'weapon_type_id'=>$weaponTypeId,'attribute_id'=>$attributeId,'item_name'=>$itemName,'material_id'=>$materialId,'rune_id'=>$runeId,'insignia_id'=>$insigniaId,'notes'=>$notes] as $k=>$v)$entry[$k]=$v;
    }
}
?>
<section class="treasure-page"><div class="treasure-heading"><h1>Edit Treasure Collection</h1><p><strong><?php echo h($entry['charname']); ?></strong></p></div>
<?php if($error!==''):?><p class="treasure-form-message treasure-error"><?php echo h($error);?></p><?php endif;?>
<form class="treasure-record-form" method="post" action="treasure-edit.php?id=<?php echo $historyId;?>"><?php echo csrf_input();?><input type="hidden" name="history_id" value="<?php echo $historyId;?>">
<div class="treasure-form-row"><label for="location_id">Location</label><select id="location_id" name="location_id" required><?php foreach($locations as $x):?><option value="<?php echo (int)$x['location_id'];?>"<?php echo te_selected($entry['location_id'],$x['location_id']);?>><?php echo h($x['location_name']);?></option><?php endforeach;?></select></div>
<div class="treasure-form-row"><label for="collected_on">Collection date</label><input id="collected_on" name="collected_on" type="date" max="<?php echo date('Y-m-d');?>" value="<?php echo h($entry['collected_on']);?>" required></div>
<div class="treasure-form-row"><label for="gold_received">Gold received</label><input id="gold_received" name="gold_received" type="number" min="0" max="16777215" value="<?php echo h((string)$entry['gold_received']);?>" required></div>
<div class="treasure-form-row"><label for="drop_type">What dropped?</label><select id="drop_type" name="drop_type" required><option value="weapon"<?php echo te_selected($entry['drop_type'],'weapon');?>>Weapon</option><option value="material"<?php echo te_selected($entry['drop_type'],'material');?>>Rare Material</option><option value="rune_insignia"<?php echo te_selected($entry['drop_type'],'rune_insignia');?>>Rune / Insignia</option><option value="nothing"<?php echo te_selected($entry['drop_type'],'nothing');?>>Nothing!</option></select></div>
<div class="treasure-drop-fields" data-drop-fields="weapon"><div class="treasure-form-row"><label>Rarity</label><select name="rarity_id"><?php foreach($rarities as $x):?><option value="<?php echo (int)$x['rarity_id'];?>"<?php echo te_selected($entry['rarity_id'],$x['rarity_id']);?>><?php echo h($x['rarity_name']);?></option><?php endforeach;?></select></div><div class="treasure-form-row"><label>Requirement</label><select name="requirement"><?php foreach($requirements as $x):?><option value="<?php echo (int)$x['requirement'];?>"<?php echo te_selected($entry['requirement'],$x['requirement']);?>><?php echo (int)$x['requirement'];?></option><?php endforeach;?></select></div><div class="treasure-form-row"><label>Weapon type</label><select id="weapon_type_id" name="weapon_type_id"><option value="">Choose item type</option><?php foreach($weaponTypes as $x):?><option value="<?php echo (int)$x['weapon_type_id'];?>"<?php echo te_selected($entry['weapon_type_id'],$x['weapon_type_id']);?>><?php echo h($x['weapon_type_name']);?></option><?php endforeach;?></select></div><div class="treasure-form-row"><label>Attribute</label><select id="attribute_id" name="attribute_id"></select></div><div class="treasure-form-row"><label>Item name</label><input name="item_name" maxlength="150" value="<?php echo h((string)($entry['item_name']??''));?>" placeholder="Optional weapon name"></div></div>
<div class="treasure-drop-fields" data-drop-fields="material"><div class="treasure-form-row"><label>Rare material</label><select name="material_id"><option value="">Choose material</option><?php foreach($materials as $x):?><option value="<?php echo (int)$x['material_id'];?>"<?php echo te_selected($entry['material_id'],$x['material_id']);?>><?php echo h($x['material_name']);?></option><?php endforeach;?></select></div></div>
<div class="treasure-drop-fields" data-drop-fields="rune_insignia"><div class="treasure-form-row"><label>Rarity</label><select name="rune_rarity_id"><option value="2"<?php echo te_selected($entry['rarity_id'],2);?>>Blue</option><option value="3"<?php echo te_selected($entry['rarity_id'],3);?>>Purple</option><option value="4"<?php echo te_selected($entry['rarity_id'],4);?>>Gold</option></select></div><div class="treasure-form-row"><label>Rune</label><select name="rune_id"><option value="0">None</option><?php foreach($runes as $x):?><option value="<?php echo (int)$x['rune_id'];?>"<?php echo te_selected($entry['rune_id'],$x['rune_id']);?>><?php echo h(($x['profession_name']==='None'?'General':$x['profession_name']).' — '.$x['rune_name']);?></option><?php endforeach;?></select></div><div class="treasure-form-row"><label>Insignia</label><select name="insignia_id"><option value="0">None</option><?php foreach($insignias as $x):?><option value="<?php echo (int)$x['insignia_id'];?>"<?php echo te_selected($entry['insignia_id'],$x['insignia_id']);?>><?php echo h(($x['profession_name']==='None'?'General':$x['profession_name']).' — '.$x['insignia_name']);?></option><?php endforeach;?></select></div></div>
<div class="treasure-form-row"><label for="notes">Notes</label><input id="notes" name="notes" maxlength="255" value="<?php echo h((string)($entry['notes']??''));?>" placeholder="Optional"></div>
<div class="treasure-form-actions"><button type="submit" name="action" value="save">Save changes</button><a class="treasure-button treasure-button-secondary" href="treasure-history.php">Cancel</a><button type="submit" name="action" value="delete" onclick="return confirm('Delete this treasure collection? This cannot be undone.');">Delete</button></div></form></section>
<script>
const attributeMap=<?php echo json_encode($attributeMap,JSON_HEX_TAG|JSON_HEX_AMP|JSON_HEX_APOS|JSON_HEX_QUOT);?>;
const drop=document.getElementById('drop_type'),weapon=document.getElementById('weapon_type_id'),attribute=document.getElementById('attribute_id');
const initialAttribute=<?php echo json_encode((string)($entry['attribute_id']??''));?>;
function syncDrop(){document.querySelectorAll('[data-drop-fields]').forEach(x=>x.hidden=x.dataset.dropFields!==drop.value);}
function syncAttributes(){const rows=attributeMap[weapon.value]||[];attribute.innerHTML='';if(!rows.length){const o=new Option(weapon.value?'No valid attributes':'Choose weapon type first','');o.disabled=true;o.selected=true;attribute.add(o);return;}attribute.add(new Option('Choose attribute','',false,false));attribute.options[0].disabled=true;for(const x of rows){const o=new Option(x.name,String(x.id));if(String(x.id)===initialAttribute)o.selected=true;attribute.add(o);}}
drop.addEventListener('change',syncDrop);weapon.addEventListener('change',()=>{syncAttributes();attribute.value='';});syncDrop();syncAttributes();
</script>
<?php include_once('footer.php');?>
