<?php

$mailHost = getenv('MAIL_HOST') ?: 'smtp.gmail.com';
$mailPort = (int)(getenv('MAIL_PORT') ?: 587);
$mailEncryption = getenv('MAIL_ENCRYPTION') ?: 'tls';
$mailUsername = getenv('MAIL_USERNAME') ?: 'victorodarve050704@gmail.com';
$mailPassword = getenv('MAIL_PASSWORD') ?: 'oshp fubh xyzy suzw';
$mailFromEmail = getenv('MAIL_FROM_EMAIL') ?: $mailUsername;
$mailFromName = getenv('MAIL_FROM_NAME') ?: 'Teacher Management System';

return [
    'host' => $mailHost,
    'port' => $mailPort,
    'encryption' => $mailEncryption,
    'username' => $mailUsername,
    'password' => $mailPassword,
    'from_email' => $mailFromEmail,
    'from_name' => $mailFromName,
];
