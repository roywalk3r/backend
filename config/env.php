<?php
function env($key, $default = null) {
    // First check environment variables
    $value = getenv($key);
    if ($value !== false) {
        return $value;
    }
    
    // Then check $_ENV superglobal
    if (isset($_ENV[$key])) {
        return $_ENV[$key];
    }
    
    // Finally check .env file
    static $envVars = null;
    if ($envVars === null) {
        $envVars = [];
        $envFile = __DIR__ . '/../.env';
        if (file_exists($envFile)) {
            $lines = file($envFile, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
            foreach ($lines as $line) {
                if (strpos($line, '=') !== false && strpos($line, '#') !== 0) {
                    list($key_env, $value_env) = explode('=', $line, 2);
                    $envVars[trim($key_env)] = trim($value_env);
                }
            }
        }
    }
    
    return $envVars[$key] ?? $default;
}
?>