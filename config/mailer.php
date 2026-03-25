<?php

use PHPMailer\PHPMailer\Exception;
use PHPMailer\PHPMailer\PHPMailer;

require_once __DIR__ . '/../PHPMailer-master/src/Exception.php';
require_once __DIR__ . '/../PHPMailer-master/src/PHPMailer.php';
require_once __DIR__ . '/../PHPMailer-master/src/SMTP.php';

function getMailConfig(): array
{
    static $mailConfig = null;

    if ($mailConfig === null) {
        $mailConfig = require __DIR__ . '/mail.php';
    }

    return $mailConfig;
}

function isPlaceholderMailValue(string $value): bool
{
    $normalizedValue = strtolower(trim($value));
    return $normalizedValue === '' || $normalizedValue === 'your-gmail@gmail.com';
}

function isMailConfigReady(array $mailConfig): bool
{
    $username = trim((string)($mailConfig['username'] ?? ''));
    $password = preg_replace('/\s+/', '', (string)($mailConfig['password'] ?? '')) ?: '';
    $fromEmail = trim((string)($mailConfig['from_email'] ?? ''));

    return !isPlaceholderMailValue($username)
        && filter_var($username, FILTER_VALIDATE_EMAIL) !== false
        && $password !== ''
        && !isPlaceholderMailValue($fromEmail)
        && filter_var($fromEmail, FILTER_VALIDATE_EMAIL) !== false;
}

function buildTeacherRegistrationMailContent(array $request, string $status): array
{
    $teacherName = trim((string)($request['name'] ?? 'Teacher'));
    $safeTeacherName = htmlspecialchars($teacherName, ENT_QUOTES, 'UTF-8');

    if ($status === 'approved') {
        return [
            'subject' => 'Your teacher registration has been approved',
            'html' => "
                <p>Hello {$safeTeacherName},</p>
                <p>Your teacher registration request has been approved by the admin.</p>
                <p>You can now log in to the system using your registered username and password.</p>
                <p>Thank you.</p>
            ",
            'text' => "Hello {$teacherName},\n\nYour teacher registration request has been approved by the admin.\nYou can now log in to the system using your registered username and password.\n\nThank you."
        ];
    }

    return [
        'subject' => 'Your teacher registration has been rejected',
        'html' => "
            <p>Hello {$safeTeacherName},</p>
            <p>Your teacher registration request has been reviewed and was not approved.</p>
            <p>Please contact the admin if you believe this decision needs clarification.</p>
            <p>Thank you.</p>
        ",
        'text' => "Hello {$teacherName},\n\nYour teacher registration request has been reviewed and was not approved.\nPlease contact the admin if you believe this decision needs clarification.\n\nThank you."
    ];
}

function sendTeacherRegistrationStatusEmail(array $request, string $status): array
{
    $recipientEmail = strtolower(trim((string)($request['email'] ?? '')));
    if ($recipientEmail === '') {
        return [
            'status' => 'skipped',
            'message' => 'Teacher registration request does not have an email address.'
        ];
    }

    $mailConfig = getMailConfig();
    if (!isMailConfigReady($mailConfig)) {
        return [
            'status' => 'skipped',
            'message' => 'Mail configuration is incomplete. Update config/mail.php with the Gmail sender address.'
        ];
    }

    $mailContent = buildTeacherRegistrationMailContent($request, $status);

    $mail = null;

    try {
        $mail = new PHPMailer(true);
        $mail->isSMTP();
        $mail->Host = (string)($mailConfig['host'] ?? 'smtp.gmail.com');
        $mail->SMTPAuth = true;
        $mail->Username = trim((string)$mailConfig['username']);
        $mail->Password = preg_replace('/\s+/', '', (string)$mailConfig['password']);
        $mail->Port = (int)($mailConfig['port'] ?? 587);
        $mail->CharSet = 'UTF-8';

        $encryption = strtolower(trim((string)($mailConfig['encryption'] ?? 'tls')));
        if ($encryption === 'ssl') {
            $mail->SMTPSecure = PHPMailer::ENCRYPTION_SMTPS;
        } else {
            $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
        }

        $mail->setFrom(trim((string)$mailConfig['from_email']), (string)($mailConfig['from_name'] ?? 'Teacher Management System'));
        $mail->addAddress($recipientEmail, trim((string)($request['name'] ?? '')));
        $mail->isHTML(true);
        $mail->Subject = $mailContent['subject'];
        $mail->Body = $mailContent['html'];
        $mail->AltBody = $mailContent['text'];
        $mail->send();

        return [
            'status' => 'sent',
            'message' => ''
        ];
    } catch (Exception $e) {
        return [
            'status' => 'failed',
            'message' => $mail instanceof PHPMailer && $mail->ErrorInfo !== '' ? $mail->ErrorInfo : $e->getMessage()
        ];
    }
}
