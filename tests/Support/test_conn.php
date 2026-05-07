#!/usr/bin/env php
<?php
require 'vendor/autoload.php';

// Load .env file
$lines = file('.env', FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
foreach($lines as $line) {
    if(strpos($line, '=') !== false && !str_starts_with(trim($line), '#')) {
        list($key, $value) = explode('=', $line, 2);
        putenv(trim($key) . '=' . trim($value));
        $_ENV[trim($key)] = trim($value);
    }
}

echo "Testing connection with both PG* and DB_URL variables set...\n";

// Test DB_URL
try {
    $pdo = new PDO(getenv('DB_URL'));
    echo "DB_URL connection: SUCCESS\n";
} catch (Exception $e) {
    echo "DB_URL connection: FAILED - " . $e->getMessage() . "\n";
}

// Test with individual params
try {
    $dsn = sprintf('pgsql:host=%s;port=%s;dbname=%s;sslmode=require', 
        getenv('PGHOST'), getenv('PGPORT'), getenv('PGDATABASE'));
    $pdo = new PDO($dsn, getenv('PGUSER'), getenv('PGPASSWORD'));
    echo "Individual params connection: SUCCESS\n";
} catch (Exception $e) {
    echo "Individual params connection: FAILED - " . $e->getMessage() . "\n";
}
?>