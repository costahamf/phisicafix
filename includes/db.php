<?php
function db(): PDO
{
    static $pdo = null;
    if ($pdo instanceof PDO) {
        return $pdo;
    }

    // Читаем настройки из .env
    $envPath = __DIR__ . '/../.env';
    $dbHost = 'localhost';
    $dbPort = '5432';
    $dbName = 'physics_db';
    $dbUser = 'postgres';
    $dbPass = '';

    if (file_exists($envPath)) {
        $env = parse_ini_file($envPath);
        $dbHost = $env['DB_HOST'] ?? 'localhost';
        $dbPort = $env['DB_PORT'] ?? '5432';
        $dbName = $env['DB_NAME'] ?? 'physics_db';
        $dbUser = $env['DB_USER'] ?? 'postgres';
        $dbPass = $env['DB_PASS'] ?? '';
    }

    $dsn = "pgsql:host={$dbHost};port={$dbPort};dbname={$dbName}";
    $pdo = new PDO($dsn, $dbUser, $dbPass);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    $pdo->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);

    return $pdo;
}