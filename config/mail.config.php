<?php
// mail.config.php

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\SMTP;
use PHPMailer\PHPMailer\Exception;

//Load Composer's autoloader (created by composer, not included with PHPMailer)
require 'vendor/autoload.php';
require_once __DIR__ . '/env.php'; // to use env()

function createMailer(): PHPMailer {
    $mail = new PHPMailer(true);

    try {
        $mail->SMTPDebug = SMTP::DEBUG_SERVER;
        $mail->isSMTP();

        $host = env('MAIL_HOST', 'localhost');
        $port = env('MAIL_PORT', 1025);
        $mail->Host = $host;
        $mail->Port = $port;

        // Use auth only if username is provided
        $username = env('MAIL_USERNAME');
        $password = env('MAIL_PASSWORD');

        if (!empty($username)) {
            $mail->SMTPAuth = true;
            $mail->Username = $username;
            $mail->Password = $password;
        } else {
            $mail->SMTPAuth = false;
        }

        // Set encryption based on host
        if (str_contains($host, 'gmail.com')) {
            $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
        } else {
            $mail->SMTPSecure = false; // Mailpit or local SMTP
        }

        $mail->setFrom(
            env('MAIL_FROM_ADDRESS', 'hello@example.com'),
            env('MAIL_FROM_NAME', 'Nananom')
        );

        $mail->Debugoutput = function ($str, $level) {
            error_log("Mail debug (level $level): $str");
        };

        $mail->Timeout = 10;

        // Disable SSL verification for local testing (Mailpit)
        $mail->SMTPOptions = [
            'ssl' => [
                'verify_peer' => false,
                'verify_peer_name' => false,
                'allow_self_signed' => true,
            ]
        ];

    } catch (Exception $e) {
        error_log("Mailer Error: " . $e->getMessage());
        throw new Exception("Failed to create mailer: " . $e->getMessage());
    }

    return $mail;
}