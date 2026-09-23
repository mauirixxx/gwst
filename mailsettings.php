<?php
$pagetitle = "E-mail server settings";
include_once ('header.php');

if (!isset($_SESSION['userid']) || !isset($_SESSION['admin']) || $_SESSION['admin'] != 1) {
    http_response_code(403); echo '<center>Access denied.</center>'; include_once ('footer.php'); exit();
}

$message = ''; $message_class = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['save_mail_settings'])) {
    $enabled = isset($_POST['enabled']) ? 1 : 0;
    $smtp_host = trim((string)($_POST['smtp_host'] ?? ''));
    $smtp_port = filter_var($_POST['smtp_port'] ?? 587, FILTER_VALIDATE_INT);
    $smtp_encryption = (string)($_POST['smtp_encryption'] ?? 'tls');
    $smtp_username = trim((string)($_POST['smtp_username'] ?? ''));
    $from_address = trim((string)($_POST['from_address'] ?? ''));
    $from_name = trim((string)($_POST['from_name'] ?? 'Guild Wars Stats Tracker'));
    $reply_to_address = trim((string)($_POST['reply_to_address'] ?? ''));

    if ($smtp_port === false || $smtp_port < 1 || $smtp_port > 65535) $message = 'SMTP port must be between 1 and 65535.';
    elseif (!in_array($smtp_encryption, array('none', 'tls', 'ssl'), true)) $message = 'Invalid SMTP encryption setting.';
    elseif ($from_address !== '' && !filter_var($from_address, FILTER_VALIDATE_EMAIL)) $message = 'From address is not a valid e-mail address.';
    elseif ($reply_to_address !== '' && !filter_var($reply_to_address, FILTER_VALIDATE_EMAIL)) $message = 'Reply-To address is not a valid e-mail address.';
    else {
        $stmt = $con->prepare("INSERT INTO mail_settings (settings_id, enabled, smtp_host, smtp_port, smtp_encryption, smtp_username, from_address, from_name, reply_to_address) VALUES (1, ?, ?, ?, ?, ?, ?, ?, ?) ON DUPLICATE KEY UPDATE enabled = VALUES(enabled), smtp_host = VALUES(smtp_host), smtp_port = VALUES(smtp_port), smtp_encryption = VALUES(smtp_encryption), smtp_username = VALUES(smtp_username), from_address = VALUES(from_address), from_name = VALUES(from_name), reply_to_address = VALUES(reply_to_address)");
        $stmt->bind_param("isisssss", $enabled, $smtp_host, $smtp_port, $smtp_encryption, $smtp_username, $from_address, $from_name, $reply_to_address);
        if ($stmt->execute()) { $message = 'E-mail server settings saved.'; $message_class = 'success'; }
        else $message = 'Unable to save e-mail server settings.';
        $stmt->close();
    }
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['send_test_email'])) {
    $test_recipient = trim((string)($_POST['test_recipient'] ?? ''));
    if (!filter_var($test_recipient, FILTER_VALIDATE_EMAIL)) $message = 'Enter a valid test recipient e-mail address.';
    else {
        require_once __DIR__ . '/includes/mailer.php';
        $subject = 'GWST SMTP test message';
        $text_body = "Success!\n\nGuild Wars Stats Tracker successfully sent this message through the configured SMTP server.\n\nIf you received this, GWST outbound e-mail is working.";
        $html_body = '<h2>GWST SMTP test successful</h2><p>Guild Wars Stats Tracker successfully sent this message through the configured SMTP server.</p><p>If you received this, <strong>GWST outbound e-mail is working.</strong></p>';
        $send_result = gwst_send_mail($con, $test_recipient, $subject, $text_body, $html_body);
        $message = $send_result['success'] ? 'Test e-mail sent to ' . $test_recipient . '.' : 'Test e-mail failed: ' . $send_result['message'];
        $message_class = $send_result['success'] ? 'success' : '';
    }
}

$settings = array('enabled'=>0,'smtp_host'=>'','smtp_port'=>587,'smtp_encryption'=>'tls','smtp_username'=>'','from_address'=>'','from_name'=>'Guild Wars Stats Tracker','reply_to_address'=>'');
$result = $con->query("SELECT enabled, smtp_host, smtp_port, smtp_encryption, smtp_username, from_address, from_name, reply_to_address FROM mail_settings WHERE settings_id = 1");
if ($result && $row = $result->fetch_assoc()) $settings = $row;
$admin_email = $_SESSION['usermail'] ?? '';
?>
<section class="mail-settings-page">
<div class="mail-settings-heading"><h1>E-mail server settings</h1><p>Configure GWST's SMTP server. The SMTP password is intentionally not stored in the database; define <code>GWST_SMTP_PASSWORD</code> in the local <code>connect.php</code>.</p></div>
<?php if ($message !== ''): ?><p class="mail-settings-message<?php echo $message_class === 'success' ? ' success' : ''; ?>"><strong><?php echo h($message); ?></strong></p><?php endif; ?>
<fieldset class="mail-settings-card"><legend>SMTP configuration</legend>
<form action="mailsettings.php" method="post" class="mail-settings-form"><?php echo csrf_input(); ?>
<label class="mail-settings-toggle" for="enabled"><input type="checkbox" id="enabled" name="enabled" value="1"<?php echo (int)$settings['enabled'] === 1 ? ' checked' : ''; ?>><span>Enable outgoing e-mail</span></label>
<div class="mail-settings-row"><label for="smtp_host">SMTP server</label><input type="text" id="smtp_host" name="smtp_host" value="<?php echo h($settings['smtp_host']); ?>" placeholder="mail.example.com"></div>
<div class="mail-settings-row"><label for="smtp_port">SMTP port</label><input type="number" id="smtp_port" name="smtp_port" min="1" max="65535" value="<?php echo (int)$settings['smtp_port']; ?>"></div>
<div class="mail-settings-row"><label for="smtp_encryption">Encryption</label><select id="smtp_encryption" name="smtp_encryption"><?php foreach (array('none'=>'None','tls'=>'TLS / STARTTLS','ssl'=>'SSL / SMTPS') as $value=>$label): ?><option value="<?php echo h($value); ?>"<?php echo $settings['smtp_encryption'] === $value ? ' selected' : ''; ?>><?php echo h($label); ?></option><?php endforeach; ?></select></div>
<div class="mail-settings-row"><label for="smtp_username">SMTP username</label><input type="text" id="smtp_username" name="smtp_username" value="<?php echo h($settings['smtp_username']); ?>" autocomplete="username"></div>
<div class="mail-settings-row"><label for="from_address">From address</label><input type="email" id="from_address" name="from_address" value="<?php echo h($settings['from_address']); ?>"></div>
<div class="mail-settings-row"><label for="from_name">From name</label><input type="text" id="from_name" name="from_name" value="<?php echo h($settings['from_name']); ?>"></div>
<div class="mail-settings-row"><label for="reply_to_address">Reply-To address</label><input type="email" id="reply_to_address" name="reply_to_address" value="<?php echo h($settings['reply_to_address']); ?>"></div>
<input type="hidden" name="save_mail_settings" value="1"><button type="submit" class="mail-settings-submit">Save e-mail settings</button></form></fieldset>
<fieldset class="mail-settings-card mail-test-card"><legend>Send test e-mail</legend><p>This sends one message using the saved settings above. Save any SMTP changes before testing.</p>
<form action="mailsettings.php" method="post" class="mail-test-form"><?php echo csrf_input(); ?><label for="test_recipient">Recipient</label><input type="email" id="test_recipient" name="test_recipient" value="<?php echo h($admin_email); ?>" required><input type="hidden" name="send_test_email" value="1"><button type="submit">Send test e-mail</button></form></fieldset>
</section>
<style>
.mail-settings-page { width: min(100%, 820px); margin: 0 auto; padding: 10px 12px 30px; }
.mail-settings-heading { text-align: center; margin-bottom: 26px; }.mail-settings-heading p { color:#b7d7e6; }.mail-settings-message{text-align:center}.mail-settings-message.success{color:#7fe8b0}
.mail-settings-card { width:100%;box-sizing:border-box;margin:26px auto 0;padding:22px 26px 26px;border:1px solid #2d6978;border-radius:7px;background:#122936;text-align:left }.mail-settings-card legend{padding:0 10px;color:#69dbe1;font-size:20px}
.mail-settings-form{display:grid;gap:16px}.mail-settings-row{display:grid;grid-template-columns:175px minmax(0,1fr);gap:16px;align-items:center}.mail-settings-row input,.mail-settings-row select,.mail-test-form input{box-sizing:border-box;width:100%;min-height:40px;padding:7px 10px;border:1px solid #547080;border-radius:5px;background:#edf2f5;color:#17242c}.mail-settings-toggle{display:inline-flex;align-items:center;gap:12px}.mail-settings-card button{min-height:40px;padding:8px 16px;border:1px solid #2999a5;border-radius:5px;background:#174454;color:#fff;font-weight:600;cursor:pointer}.mail-test-form{display:grid;grid-template-columns:90px minmax(0,1fr) auto;gap:12px;align-items:center}@media(max-width:650px){.mail-settings-row,.mail-test-form{grid-template-columns:1fr}.mail-settings-card button{width:100%}}
</style>
<script>(function(){var e=document.getElementById('smtp_encryption'),p=document.getElementById('smtp_port');if(!e||!p)return;var s={none:'25',tls:'587',ssl:'465'};e.addEventListener('change',function(){if(Object.prototype.hasOwnProperty.call(s,e.value))p.value=s[e.value];});}());</script>
<?php include_once ('footer.php'); ?>