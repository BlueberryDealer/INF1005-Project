<?php

function db_config_path(): string
{
    $serverConfig = '/var/www/private/db-config.ini';
    $localConfig = __DIR__ . '/db-config.ini';

    if (file_exists($serverConfig)) {
        return $serverConfig;
    }

    if (file_exists($localConfig)) {
        return $localConfig;
    }

    throw new RuntimeException('Database config file not found.');
}

function db_connect(): mysqli
{
    $config = parse_ini_file(db_config_path());

    if (!$config) {
        throw new RuntimeException('Failed to read database config file.');
    }

    $conn = new mysqli(
        $config['servername'],
        $config['username'],
        $config['password'],
        $config['dbname']
    );

    if ($conn->connect_error) {
        throw new RuntimeException('Database connection failed: ' . $conn->connect_error);
    }

    return $conn;
}

$conn = db_connect();
?>
