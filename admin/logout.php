<?php
require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../classes/Auth.php';

$auth = new \App\Auth();
$result = $auth->logout();

// Redirect to login page
header('Location: index.php?message=' . urlencode($result['message']));
exit();
?>