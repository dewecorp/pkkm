<?php
require_once __DIR__ . '/../config/database.php';

try {
    $pdo = getDbConnection();
    $sql = file_get_contents(__DIR__ . '/schema.sql');
    $pdo->exec($sql);
    echo "Database & tables created successfully!\n";
} catch (Exception $e) {
    echo "Migration failed: " . $e->getMessage() . "\n";
}
