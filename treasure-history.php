<?php
$pagetitle = 'Treasure History';
include_once('header.php');
if (empty($_SESSION['userid'])) { exit; }
$userid=(int)$_SESSION['userid']; $accid=(int)($_SESSION['prefaccid']??0); $charid=(int)($_SESSION['prefcharid']??0); $charname=(string)($_SESSION['prefcharname']??'');
$characterValid=false;
if($accid>0&&$charid>0){$check=$con->prepare('SELECT 1 FROM gwchars WHERE charid=? AND accid=? AND userid=? LIMIT 1');$check->bind_param('iii',$charid,$accid,$userid);$check->execute();$characterValid=(bool)$check->get_result()->fetch_row();$check->close();}
$totalRuns=0;$totalGold=0;$history=[];
if($characterValid){
$summary=$con->prepare('SELECT COUNT(*) total_runs,COALESCE(SUM(gold_received),0) total_gold FROM gwtreasure_history WHERE userid=? AND charid=?');$summary->bind_param('ii',$userid,$charid);$summary->execute();$s=$summary->get_result()->fetch_assoc();$totalRuns=(int)($s['total_runs']??0);$totalGold=(int)($s['total_gold']??0);$summary->close();
$stmt=$con->prepare("SELECT h.treasure_history_id,h.collected_on,h.gold_received,h.drop_type,h.drop_description,h.item_name,h.notes,l.location_name,l.wiki_url,ra.rarity_name,wt.weapon_type_name,at.attribute_name,h.requirement,ma.material_name,ru.rune_name,rp.profession_name rune_profession,ins.insignia_name,ip.profession_name insignia_profession FROM gwtreasure_history h JOIN gwtreasure_locations l ON l.location_id=h.location_id LEFT JOIN gwtreasure_rarities ra ON ra.rarity_id=h.rarity_id LEFT JOIN gwtreasure_weapon_types wt ON wt.weapon_type_id=h.weapon_type_id LEFT JOIN gwtreasure_attributes at ON at.attribute_id=h.attribute_id LEFT JOIN gwtreasure_materials ma ON ma.material_id=h.material_id LEFT JOIN gwtreasure_runes ru ON ru.rune_id=h.rune_id LEFT JOIN gwtreasure_professions rp ON rp.profession_id=ru.profession_id LEFT JOIN gwtreasure_insignias ins ON ins.insignia_id=h.insignia_id LEFT JOIN gwtreasure_professions ip ON ip.profession_id=ins.profession_id WHERE h.userid=? AND h.charid=? ORDER BY h.collected_on DESC,h.treasure_history_id DESC");
$stmt->bind_param('ii',$userid,$charid);$stmt->execute();$r=$stmt->get_result();while($row=$r->fetch_assoc()){$history[]=$row;}$stmt->close();}
function treasure_drop_label(string $type):string{$labels=['weapon'=>'Weapon','material'=>'Rare Material','rune_insignia'=>'Rune / Insignia','nothing'=>'Nothing!'];return $labels[$type]??ucfirst(str_replace('_',' ',$type));}
function treasure_drop_description(array $row):string{
    if($row['drop_type']==='weapon' && $row['weapon_type_name']){
        $parts=[]; if($row['rarity_name'])$parts[]=$row['rarity_name']; if($row['requirement']!==null)$parts[]='R'.(int)$row['requirement']; if($row['attribute_name'])$parts[]=$row['attribute_name']; $parts[]=$row['weapon_type_name']; if($row['item_name'])$parts[]='“'.$row['item_name'].'”'; return implode(' • ',$parts);
    }
    if($row['drop_type']==='material' && $row['material_name'])return $row['material_name'];
    if($row['drop_type']==='rune_insignia'){
        $parts=[]; if($row['rarity_name'])$parts[]=$row['rarity_name']; if($row['rune_name'])$parts[]=(($row['rune_profession']&&$row['rune_profession']!=='None')?$row['rune_profession'].' — ':'').$row['rune_name'].' Rune'; if($row['insignia_name'])$parts[]=(($row['insignia_profession']&&$row['insignia_profession']!=='None')?$row['insignia_profession'].' — ':'').$row['insignia_name']; if($parts)return implode(' • ',$parts);
    }
    if($row['drop_type']==='nothing')return 'Nothing dropped';
    return (string)($row['drop_description']??'');
}
?>
<section class="treasure-page treasure-history-page"><div class="treasure-heading"><h1>Treasure History</h1><?php if($characterValid): ?><p>Collection history for <strong><?php echo h($charname); ?></strong>.</p><?php else: ?><p>Select an account and character in the header to view treasure history.</p><?php endif; ?></div>
<?php if($characterValid): ?><div class="treasure-summary"><div class="treasure-summary-card"><strong><?php echo number_format($totalRuns); ?></strong><span>Total collections</span></div><div class="treasure-summary-card"><strong><?php echo number_format($totalGold); ?></strong><span>Total gold collected</span></div></div>
<?php if(!$history): ?><div class="treasure-empty"><p>No treasure collections have been recorded for <?php echo h($charname); ?> yet.</p><a class="treasure-button" href="treasures.php">Back to Treasure Tracker</a></div>
<?php else: ?><div class="treasure-history-wrap"><table class="treasure-history-table"><thead><tr><th>Date</th><th>Location</th><th>Gold</th><th>Drop</th><th>Description</th><th>Notes</th></tr></thead><tbody>
<?php foreach($history as $row): $description=treasure_drop_description($row); ?><tr><td class="treasure-history-date"><?php echo h(date('M j, Y',strtotime($row['collected_on']))); ?></td><td><?php if(!empty($row['wiki_url'])): ?><a class="treasure-wiki-link" href="<?php echo h($row['wiki_url']); ?>" target="_blank" rel="noopener noreferrer"><?php echo h($row['location_name']); ?></a><?php else: echo h($row['location_name']); endif; ?></td><td class="treasure-history-gold"><?php echo number_format((int)$row['gold_received']); ?></td><td><?php echo h(treasure_drop_label((string)$row['drop_type'])); ?></td><td><?php echo $description!==''?h($description):'&mdash;'; ?></td><td><?php echo $row['notes']!==null&&$row['notes']!==''?h($row['notes']):'&mdash;'; ?></td></tr><?php endforeach; ?>
</tbody></table></div><?php endif; ?><div class="treasure-history-actions"><a class="treasure-button treasure-button-secondary" href="treasures.php">Back to Treasure Tracker</a></div><?php endif; ?></section>
<?php include_once('footer.php'); ?>
