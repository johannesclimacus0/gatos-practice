<?php

require_once __DIR__ . '/vendor/autoload.php';

use Doctrine\Migrations\DependencyFactory;
use Doctrine\Migrations\Configuration\Migration\PhpFile;
use Doctrine\Migrations\Configuration\Connection\ExistingConnection;
use Doctrine\DBAL\DriverManager;
use Dotenv\Dotenv;

$dotenv = Dotenv::createImmutable(__DIR__);
$dotenv->load();

$driver = $_ENV['DB_DRIVER'] ?? 'pdo_mysql';

if ($driver === 'mysql') {
    $driver = 'pdo_mysql';
}

$dbParams = [
    'driver' => $driver,
    'host' => $_ENV['DB_HOST'],
    'port' => (int) ($_ENV['DB_PORT'] ?? 3306),
    'dbname' => $_ENV['DB_DATABASE'],
    'user' => $_ENV['DB_USER'],
    'password' => $_ENV['DB_PASS'],
    'charset' => 'utf8mb4',
];

$connection = DriverManager::getConnection($dbParams);

$migrationConfig = new PhpFile(__DIR__ . '/migrations.php');

return DependencyFactory::fromConnection(
    $migrationConfig,
    new ExistingConnection($connection)
);
