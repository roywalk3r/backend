<?php

require_once __DIR__ . '/../vendor/autoload.php';
require_once __DIR__ . '/../config/mail.config.php';
require_once __DIR__ . '/../classes/EmailService.php';

use App\EmailService;

try {
    $emailService = new EmailService();
    $userData = [
        'email' => 'rseann81@gmail.com',
        'first_name' => 'Test',
        'last_name' => 'User'
    ];
    $emailService->sendWelcomeEmail($userData);
    echo "✅ Email sent successfully!\n";
} catch (Exception $e) {
    echo "❌ Error: " . $e->getMessage() . "\n";
    if (isset($mail)) {
        echo "SMTP Error: " . $mail->ErrorInfo . "\n";
    }
}