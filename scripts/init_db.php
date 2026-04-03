<?php
// ============================================================
// GROCEESARY – SQLite Database Initializer
// ============================================================

// STEP 1: Define SQLite Constants BEFORE including config
define('DB_TYPE', 'sqlite');
define('DB_PATH', __DIR__ . '/../database/groceesary.sqlite');
define('DEBUG_MODE', true);

require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../config/database.php';

echo "🚀 Starting Database Initialization (SQLite Mode)...\n";

$dbFile = DB_PATH;
if (file_exists($dbFile)) {
    unlink($dbFile);
    echo "⚠️ Existing database file removed.\n";
}

try {
    $db = Database::getInstance();
    
    // Read schema file
    $schemaFile = __DIR__ . '/../database/schema.sql';
    if (!file_exists($schemaFile)) {
        die("❌ Error: schema.sql not found at $schemaFile\n");
    }

    echo "📖 Reading schema.sql...\n";
    $sql = file_get_contents($schemaFile);

    // Basic MySQL to SQLite syntax conversion
    $search = [
        '/INT UNSIGNED AUTO_INCREMENT PRIMARY KEY/i',
        '/INT UNSIGNED AUTO_INCREMENT/i',
        '/INT UNSIGNED/i',
        '/TINYINT\(1\)/i',
        '/ENUM\([^)]+\)/i',
        '/DECIMAL\([0-9,]+\)/i',
        '/ENGINE=InnoDB/i',
        '/DEFAULT CHARSET=[a-z0-9]+/i',
        '/COLLATE=[a-z0-9_]+/i',
        '/ON UPDATE CURRENT_TIMESTAMP/i',
        '/JSON/i',
        '/INDEX `idx_[a-z_]+` \(`[a-z_]+`\)/i',
        '/FULLTEXT INDEX `[a-z_]+` \([^)]+\)/i',
        '/UNIQUE KEY `[a-z_]+` \([^)]+\)/i'
    ];
    $replace = [
        'INTEGER PRIMARY KEY AUTOINCREMENT',
        'INTEGER',
        'INTEGER',
        'INTEGER',
        'TEXT',
        'NUMERIC',
        '',
        '',
        '',
        '',
        'TEXT',
        '',
        '',
        ''
    ];

    $sqlite_queries = preg_replace($search, $replace, $sql);
    
    // Split by semicolon and execute
    $queries = explode(';', $sqlite_queries);
    foreach ($queries as $query) {
        $query = trim($query);
        if (!empty($query)) {
            $db->execute($query, "");
        }
    }

    echo "✅ SQLite Database initialized successfully at: $dbFile\n";
    
    // Optional: Add seed data?
    $seedFile = __DIR__ . '/../database/seed_data.sql';
    if (file_exists($seedFile)) {
        echo "🌱 Importing seed data...\n";
        $seedSql = file_get_contents($seedFile);
        $seed_queries = explode(';', preg_replace($search, $replace, $seedSql));
        foreach ($seed_queries as $q) {
            $q = trim($q);
            if (!empty($q)) $db->execute($q, "");
        }
        echo "✅ Seed data imported.\n";
    }

} catch (Exception $e) {
    die("❌ Error: " . $e->getMessage() . "\n");
}
?>
