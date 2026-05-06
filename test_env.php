<?php
$lines = file('.env', FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
foreach($lines as $line) {
    if(strpos($line, '=') !== false && !str_starts_with(trim($line), '#')) {
        list($key, $value) = explode('=', $line, 2);
        putenv(trim($key) . '=' . trim($value));
        $_ENV[trim($key)] = trim($value);
    }
}
echo 'DB_CONNECTION: ' . getenv('DB_CONNECTION') . PHP_EOL;
echo 'DB_HOST: ' . getenv('DB_HOST') . PHP_EOL;
echo 'DB_PORT: ' . getenv('DB_PORT') . PHP_EOL;
echo 'DB_DATABASE: ' . getenv('DB_DATABASE') . PHP_EOL;
echo 'DB_USERNAME: ' . getenv('DB_USERNAME') . PHP_EOL;
echo 'DB_PASSWORD: ' . getenv('DB_PASSWORD') . PHP_EOL;
echo 'DB_SSL_MODE: ' . getenv('DB_SSL_MODE') . PHP_EOL;
?>