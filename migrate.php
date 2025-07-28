<?php

require_once __DIR__ . '/config/database.php';

file_put_contents(__DIR__ . '/storage/migrations_log.log', "Connected to '$database' successfully.\n", FILE_APPEND);
echo "✅ Database '$database' created or already exists.\n";
echo "✅ Connected to '$database' successfully.\n";

$migrationsDir = __DIR__ . '/migrations';
if (!is_dir($migrationsDir)) {
    file_put_contents(__DIR__ . '/storage/migrations_log.log', "❌ Migrations directory not found.\n", FILE_APPEND);
    exit("❌ Migrations directory not found.\n");
}

$migrationFiles = glob($migrationsDir . '/*.php');
if (empty($migrationFiles)) {
    echo "⚠️ No migration files found.\n";
    exit;
}

// Ensure 'migrations' table exists
$conn->query("CREATE TABLE IF NOT EXISTS migrations (
    id INT AUTO_INCREMENT PRIMARY KEY,
    migration VARCHAR(255) NOT NULL,
    batch INT NOT NULL,
    migrated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
)");

$result = $conn->query("SELECT migration FROM migrations");
$executed = [];
while ($row = $result->fetch_assoc()) {
    $executed[] = $row['migration'];
}

// Determine current batch number
$batchResult = $conn->query("SELECT MAX(batch) AS max_batch FROM migrations");
$batchRow = $batchResult->fetch_assoc();
$batch = $batchRow['max_batch'] ? $batchRow['max_batch'] + 1 : 1;

foreach ($migrationFiles as $filePath) {
    $filename = basename($filePath);

    if (in_array($filename, $executed)) {
        echo "⏩ Skipping $filename (already migrated)\n";
        continue;
    }

    $migration = require $filePath;

    if (is_callable($migration)) {
        $migration($conn);

        $stmt = $conn->prepare("INSERT INTO migrations (migration, batch) VALUES (?, ?)");
        $stmt->bind_param("si", $filename, $batch);
        $stmt->execute();

        echo "✅ Migrated $filename\n";
        file_put_contents(__DIR__ . '/storage/migrations_log.log', "✅ Migrated $filename\n", FILE_APPEND);
    } else {
        echo "❌ $filename is not a valid migration file.\n";
        file_put_contents(__DIR__ . '/storage/migrations_log.log', "❌ Skipped $filename: Not a valid migration.\n", FILE_APPEND);
    }
}
