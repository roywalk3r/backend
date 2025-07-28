<?php
// mail.config.php

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\SMTP;
use PHPMailer\PHPMailer\Exception;

//Load Composer's autoloader (created by composer, not included with PHPMailer)
require 'vendor/autoload.php';
require_once __DIR__ . '/config.php'; // to use env()

function createMailer(): PHPMailer {
    $mail = new PHPMailer(true); // Enable exceptions

    try {
        // Server settings
        $mail->SMTPDebug = SMTP::DEBUG_CONNECTION;  // Show connection details
        $mail->isSMTP();
        
        // Gmail SMTP configuration
        $mail->Host = env('MAIL_HOST', 'smtp.gmail.com');
        $mail->SMTPAuth = true;
        $mail->Username = env('MAIL_USERNAME');
        $mail->Password = env('MAIL_PASSWORD');
        $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
        $mail->Port = env('MAIL_PORT', 587);  // TLS port
        
        // Sender
        $mail->setFrom(env('MAIL_FROM_ADDRESS', 'testpjmail@gmail.com'), env('MAIL_FROM_NAME', 'Nananom Farms'), 'Nananom Farms');
        
        // Debug output to error log
        $mail->Debugoutput = function($str, $level) {
            error_log("Mail debug (level $level): $str");
        };
        
        // Timeout settings
        $mail->Timeout = 10;  // 10 second timeout
        
        // SSL/TLS settings
        $mail->SMTPOptions = [
            'ssl' => [
                'verify_peer' => true,
                'verify_peer_name' => true,
                'allow_self_signed' => false,
            ]
        ];

    } catch (Exception $e) {
        error_log("Mailer Error: " . $e->getMessage());
        error_log("SMTP Error: " . ($mail->ErrorInfo ?? 'No error info available'));
        throw new Exception("Failed to create mailer: " . $e->getMessage());
    }

    return $mail;
}
