<?php
require __DIR__.'/bootstrap/app.php';

$app = Illuminate\Foundation\Application::bootstrap();

try {
    $pdo = Illuminate\Support\Facades\DB::connection()->getPdo();
    echo "Laravel DB connection: SUCCESS\n";
    echo "Driver: " . $pdo->getAttribute(PDO::ATTR_DRIVER_NAME) . "\n";
    echo "Server version: " . $pdo->getAttribute(PDO::ATTR_SERVER_VERSION) . "\n";
} catch (Exception $e) {
    echo "Laravel DB connection: FAILED - " . $e->getMessage() . "\n";
}
?>