<?php
// Simple SMTP connection test
$smtp_server = 'smtp.gmail.com';
$smtp_port = 587;

$socket = @fsockopen($smtp_server, $smtp_port, $errno, $errstr, 10);

if (!$socket) {
    echo "Could not connect to {$smtp_server}:{$smtp_port}\n";
    echo "Error {$errno}: {$errstr}\n";
} else {
    echo "Successfully connected to {$smtp_server}:{$smtp_port}\n";
    fclose($socket);
}

// Test common SMTP ports
$ports = [25, 465, 587, 2525];
foreach ($ports as $port) {
    $socket = @fsockopen($smtp_server, $port, $errno, $errstr, 5);
    if ($socket) {
        echo "✅ Port {$port} is open\n";
        fclose($socket);
    } else {
        echo "❌ Port {$port} is closed or blocked\n";
    }
}
