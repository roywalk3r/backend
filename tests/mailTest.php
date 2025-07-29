<?php

require_once __DIR__ . '/../vendor/autoload.php';
require_once __DIR__ . '/../config/mail.config.php';

try {
    $mail = createMailer();
    
    // Set recipient (replace with actual email for testing)
    $mail->addAddress('rseann81@gmail.com', 'Test Recipient');
    
    // Email content
    $mail->Subject = 'Test Email from Nananom Farms';
    $mail->Body    = '<h1>Test Email</h1><p>This is a test email from Nananom Farms.</p>';
    $mail->AltBody = 'This is a test email from Nananom Farms.';
    
    // Send the email
    if ($mail->send()) {
        echo "✅ Email sent successfully!\n";
    } else {
        echo "❌ Email failed to send.\n";
        echo "Error: " . $mail->ErrorInfo . "\n";
    }
    
} catch (Exception $e) {
    echo "❌ Error: " . $e->getMessage() . "\n";
    if (isset($mail)) {
        echo "SMTP Error: " . $mail->ErrorInfo . "\n";
    }
}

echo "Test completed.\n";