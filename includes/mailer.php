<?php

use PHPMailer\PHPMailer\Exception as PHPMailerException;
use PHPMailer\PHPMailer\PHPMailer;

/**
 * Send an e-mail using GWST's database-backed SMTP settings.
 *
 * The SMTP password is deliberately kept out of the database and must be
 * defined locally as GWST_SMTP_PASSWORD (normally in connect.php).
 *
 * @return array{success: bool, message: string}
 */
function gwst_send_mail(mysqli $con, string $recipient, string $subject, string $text_body, ?string $html_body = null): array
{
    if (!filter_var($recipient, FILTER_VALIDATE_EMAIL)) {
        return array('success' => false, 'message' => 'Recipient e-mail address is invalid.');
    }

    $autoload = dirname(__DIR__) . '/vendor/autoload.php';
    if (!is_file($autoload)) {
        return array('success' => false, 'message' => 'Composer dependencies are not installed. Run composer install first.');
    }
    require_once $autoload;

    $result = $con->query("SELECT enabled, smtp_host, smtp_port, smtp_encryption, smtp_username, from_address, from_name, reply_to_address FROM mail_settings WHERE settings_id = 1");
    if (!$result || !($settings = $result->fetch_assoc())) {
        return array('success' => false, 'message' => 'E-mail server settings are unavailable.');
    }

    if ((int)$settings['enabled'] !== 1) {
        return array('success' => false, 'message' => 'Outgoing e-mail is disabled.');
    }
    if ($settings['smtp_host'] === '' || $settings['from_address'] === '') {
        return array('success' => false, 'message' => 'SMTP host and From address must be configured.');
    }
    if (!filter_var($settings['from_address'], FILTER_VALIDATE_EMAIL)) {
        return array('success' => false, 'message' => 'Configured From address is invalid.');
    }
    if (!defined('GWST_SMTP_PASSWORD')) {
        return array('success' => false, 'message' => 'GWST_SMTP_PASSWORD is not defined in the local configuration.');
    }

    try {
        $mail = new PHPMailer(true);
        $mail->isSMTP();
        $mail->Host = $settings['smtp_host'];
        $mail->Port = (int)$settings['smtp_port'];
        $mail->Timeout = 15;
        $mail->SMTPAuth = ($settings['smtp_username'] !== '');
        if ($mail->SMTPAuth) {
            $mail->Username = $settings['smtp_username'];
            $mail->Password = GWST_SMTP_PASSWORD;
        }

        if ($settings['smtp_encryption'] === 'ssl') {
            $mail->SMTPSecure = PHPMailer::ENCRYPTION_SMTPS;
        } elseif ($settings['smtp_encryption'] === 'tls') {
            $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
        } else {
            $mail->SMTPSecure = '';
            $mail->SMTPAutoTLS = false;
        }

        $mail->CharSet = PHPMailer::CHARSET_UTF8;
        $mail->setFrom($settings['from_address'], $settings['from_name']);
        if ($settings['reply_to_address'] !== '') {
            if (!filter_var($settings['reply_to_address'], FILTER_VALIDATE_EMAIL)) {
                return array('success' => false, 'message' => 'Configured Reply-To address is invalid.');
            }
            $mail->addReplyTo($settings['reply_to_address']);
        }
        $mail->addAddress($recipient);
        $mail->Subject = $subject;

        if ($html_body !== null && $html_body !== '') {
            $mail->isHTML(true);
            $mail->Body = $html_body;
            $mail->AltBody = $text_body;
        } else {
            $mail->isHTML(false);
            $mail->Body = $text_body;
        }

        $mail->send();
        return array('success' => true, 'message' => 'Message accepted by the SMTP server.');
    } catch (PHPMailerException $e) {
        error_log('GWST mail send failed: ' . $e->getMessage());
        return array('success' => false, 'message' => 'SMTP send failed. Check the server log for details.');
    } catch (Throwable $e) {
        error_log('GWST mail error: ' . $e->getMessage());
        return array('success' => false, 'message' => 'Unable to send e-mail. Check the server log for details.');
    }
}
