<?php
/**
 * Mail Configuration Example
 * Copy this file to mail-config.php and update with your credentials
 *
 * IMPORTANT:
 * 1. Copy: cp mail-config.example.php mail-config.php
 * 2. Edit mail-config.php with your actual credentials
 * 3. Never commit mail-config.php to Git (it's in .gitignore)
 */

// Hostinger Mail Configuration
define('MAIL_CONFIG', [
    'imap' => [
        'host' => 'imap.hostinger.com',
        'port' => 993,
        'ssl' => true,
        'username' => 'your-email@yourdomain.com',  // Change this
        'password' => 'your-password-here'           // Change this
    ],
    'smtp' => [
        'host' => 'smtp.hostinger.com',
        'port' => 465,
        'ssl' => true,
        'username' => 'your-email@yourdomain.com',  // Change this
        'password' => 'your-password-here'           // Change this
    ],
    'pop' => [
        'host' => 'pop.hostinger.com',
        'port' => 995,
        'ssl' => true,
        'username' => 'your-email@yourdomain.com',  // Change this
        'password' => 'your-password-here'           // Change this
    ]
]);

// Mailbox folders
define('MAIL_FOLDERS', [
    'INBOX' => 'Gələnlər',
    'SENT' => 'Göndərilənlər',
    'DRAFTS' => 'Qaralamalar',
    'SPAM' => 'Spam',
    'TRASH' => 'Zibil'
]);
?>
