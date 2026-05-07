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

$dburl = getenv('DB_URL');
echo "DB_URL: " . $dburl . PHP_EOL;

try {
    $pdo = new PDO($dburl);
    echo "SUCCESS: Connected to database" . PHP_EOL;
    echo "Driver: " . $pdo->getAttribute(PDO::ATTR_DRIVER_NAME) . PHP_EOL;
    echo "Server version: " . $pdo->getAttribute(PDO::ATTR_SERVER_VERSION) . PHP_EOL;
} catch (Exception $e) {
    echo "FAILED: " . $e->getMessage() . PHP_EOL;
    echo "Error code: " . $e->getCode() . PHP_EOL;
}
?>