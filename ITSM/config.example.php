<?php
/*
 * Copy this file to config.local.php and fill in the real values.
 * config.local.php is ignored by Git - never commit real passwords.
 */
return [
    'db' => [
        'host' => 'localhost',
        'user' => 'root',
        'pass' => '',
        'name' => 'your_database_name',
    ],
    'mail' => [
        'host'     => 'smtp.gmail.com',
        'port'     => 587,
        'username' => 'your-account@gmail.com',
        'password' => 'xxxx xxxx xxxx xxxx',          // Gmail App Password
        // Optional: used by ticket / ticket message / ticket status emails.
        // Leave out to use 'password' above.
        'password_tickets' => 'xxxx xxxx xxxx xxxx',
    ],
];
