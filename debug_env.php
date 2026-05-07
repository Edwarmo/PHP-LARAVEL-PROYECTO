<?php
echo "Reading .env file...\n";
$lines = file('.env', FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
echo "Got " . count($lines) . " lines\n";

foreach($lines as $line_num => $line) {
    echo "Line {$line_num}: '" . $line . "'\n";
    if(strpos($line, '=') !== false && !str_starts_with(trim($line), '#')) {
        list($key, $value) = explode('=', $line, 2);
        $key = trim($key);
        $value = trim($value);
        echo "  Key: [{$key}]\n";
        echo "  Value: [{$value}]\n";
        putenv($key . '=' . $value);
        $_ENV[$key] = $value;
    }
}

echo "\nEnvironment variables:\n";
echo 'DB_CONNECTION: "' . getenv('DB_CONNECTION') . '"\n';
echo 'DB_URL: "' . getenv('DB_URL') . '"\n';

echo "\nTesting PDO connection...\n";
require 'vendor/autoload.php';

try {
    $dsn = getenv('DB_URL');
    echo "DSN: {$dsn}\n";
    $pdo = new PDO($dsn);
    echo "SUCCESS: Connected to database\n";
    echo "Driver: " . $pdo->getAttribute(PDO::ATTR_DRIVER_NAME) . "\n";
} catch (Exception $e) {
    echo "FAILED: " . $e->getMessage() . "\n";
    echo "Error code: " . $e->getCode() . "\n";
}
?>