<?php
require 'vendor/autoload.php';

try {
    $pdo = new PDO(getenv('DB_URL'));
    echo "DB_URL connection: SUCCESS\n";
    echo "Server version: " . $pdo->getAttribute(PDO::ATTR_SERVER_VERSION) . "\n";
} catch (Exception $e) {
    echo "DB_URL connection: FAILED - " . $e->getMessage() . "\n";
    var_dump(getenv('DB_URL'));
}
?>