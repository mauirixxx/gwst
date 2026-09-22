<?php
$pagetitle = "E-mail server settings";
include_once ('header.php');

if (!isset($_SESSION['userid']) || !isset($_SESSION['admin']) || $_SESSION['admin'] != 1) {
    http_response_code(403);
    echo '<center>Access denied.</center>';
    include_once ('footer.php');
    exit();
}

$message = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['save_mail_settings'])) {
    $enabled = isset($_POST['enabled']) ? 1 : 0;
    $smtp_host = trim($_POST['smtp_host'] ?? '');
    $smtp_port = (int)($_POST['smtp_port'] ?? 587);
    $smtp_encryption = $_POST['smtp_encryption'] ?? 'tls';
    $smtp_username = trim($_POST['smtp_username'] ?? '');
    $from_address = trim($_POST['from_address'] ?? '');
    $from_name = trim($_POST['from_name'] ?? 'Guild Wars Stats Tracker');
    $reply_to_address = trim($_POST['reply_to_address'] ?? '');

    if ($smtp_port < 1 || $smtp_port > 65535) {
        $message = 'SMTP port must be between 1 and 65535.';
    } elseif (!in_array($smtp_encryption, array('none', 'tls', 'ssl'), true)) {
        $message = 'Invalid SMTP encryption setting.';
    } elseif ($from_address !== '' && !filter_var($from_address, FILTER_VALIDATE_EMAIL)) {
        $message = 'From address is not a valid e-mail address.';
    } elseif ($reply_to_address !== '' && !filter_var($reply_to_address, FILTER_VALIDATE_EMAIL)) {
        $message = 'Reply-To address is not a valid e-mail address.';
    } else {
        $stmt = $con->prepare("INSERT INTO mail_settings (settings_id, enabled, smtp_host, smtp_port, smtp_encryption, smtp_username, from_address, from_name, reply_to_address) VALUES (1, ?, ?, ?, ?, ?, ?, ?, ?) ON DUPLICATE KEY UPDATE enabled = VALUES(enabled), smtp_host = VALUES(smtp_host), smtp_port = VALUES(smtp_port), smtp_encryption = VALUES(smtp_encryption), smtp_username = VALUES(smtp_username), from_address = VALUES(from_address), from_name = VALUES(from_name), reply_to_address = VALUES(reply_to_address)");
        $stmt->bind_param("isisssss", $enabled, $smtp_host, $smtp_port, $smtp_encryption, $smtp_username, $from_address, $from_name, $reply_to_address);
        if ($stmt->execute()) {
            $message = 'E-mail server settings saved.';
        } else {
            $message = 'Unable to save e-mail server settings.';
        }
        $stmt->close();
    }
}

$settings = array(
    'enabled' => 0,
    'smtp_host' => '',
    'smtp_port' => 587,
    'smtp_encryption' => 'tls',
    'smtp_username' => '',
    'from_address' => '',
    'from_name' => 'Guild Wars Stats Tracker',
    'reply_to_address' => ''
);

$result = $con->query("SELECT enabled, smtp_host, smtp_port, smtp_encryption, smtp_username, from_address, from_name, reply_to_address FROM mail_settings WHERE settings_id = 1");
if ($result && $row = $result->fetch_assoc()) {
    $settings = $row;
}

echo '<section class="content-card"><h2>E-mail server settings</h2>';
if ($message !== '') {
    echo '<p><strong>' . h($message) . '</strong></p>';
}
echo '<p>Configure GWST\'s SMTP server here. The SMTP password is intentionally not stored in the database; define <code>GWST_SMTP_PASSWORD</code> in the local <code>connect.php</code>.</p>';
echo '<form action="mailsettings.php" method="post">';
echo '<table border="1">';
echo '<tr><th>Enable outgoing e-mail</th><td><input type="checkbox" name="enabled" value="1"' . ((int)$settings['enabled'] === 1 ? ' checked' : '') . '></td></tr>';
echo '<tr><th>SMTP server</th><td><input type="text" name="smtp_host" size="40" value="' . h($settings['smtp_host']) . '" placeholder="mail.example.com"></td></tr>';
echo '<tr><th>SMTP port</th><td><input type="number" name="smtp_port" min="1" max="65535" value="' . (int)$settings['smtp_port'] . '"></td></tr>';
echo '<tr><th>Encryption</th><td><select name="smtp_encryption">';
foreach (array('none' => 'None', 'tls' => 'TLS / STARTTLS', 'ssl' => 'SSL / SMTPS') as $value => $label) {
    echo '<option value="' . h($value) . '"' . ($settings['smtp_encryption'] === $value ? ' selected' : '') . '>' . h($label) . '</option>';
}
echo '</select></td></tr>';
echo '<tr><th>SMTP username</th><td><input type="text" name="smtp_username" size="40" value="' . h($settings['smtp_username']) . '"></td></tr>';
echo '<tr><th>From address</th><td><input type="email" name="from_address" size="40" value="' . h($settings['from_address']) . '"></td></tr>';
echo '<tr><th>From name</th><td><input type="text" name="from_name" size="40" value="' . h($settings['from_name']) . '"></td></tr>';
echo '<tr><th>Reply-To address</th><td><input type="email" name="reply_to_address" size="40" value="' . h($settings['reply_to_address']) . '"></td></tr>';
echo '</table><br />';
echo '<input type="hidden" name="save_mail_settings" value="1">';
echo '<input type="submit" value="Save e-mail settings">';
echo '</form>';
echo '<p><small>Sending mail is not enabled by this page alone. PHPMailer integration and the test-message function are the next step.</small></p>';
echo '</section>';

include_once ('footer.php');
?>
