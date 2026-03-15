<?php
$serverConfig = '/var/www/private/db-config.ini';
$localConfig  = __DIR__ . '/db-config.ini';

$configPath = file_exists($serverConfig) ? $serverConfig : $localConfig;

if (!file_exists($configPath)) {
    die("Database config file not found.");
}

$config = parse_ini_file($configPath);

if (!$config) {
    die("Failed to read database config file.");
}

$conn = new mysqli(
    $config['servername'],
    $config['username'],
    $config['password'],
    $config['dbname']
);

if ($conn->connect_error) {
    die("Database connection failed: " . $conn->connect_error);
}
?>