<?php

namespace Tests\Support;

use Illuminate\Database\Capsule\Manager as Capsule;
use Illuminate\Database\Schema\Blueprint;
use PHPUnit\Framework\TestCase;

abstract class DatabaseTestCase extends TestCase
{
    private static bool $databaseBooted = false;

    public static function setUpBeforeClass(): void
    {
        parent::setUpBeforeClass();

        $dotenv = \Dotenv\Dotenv::createImmutable(dirname(__DIR__, 2));
        $dotenv->safeLoad();

        if (self::$databaseBooted) {
            return;
        }

        $host = self::requiredEnvironmentValue('DB_TEST_HOST');
        $port = self::requiredEnvironmentValue('DB_TEST_PORT');
        $database = self::requiredEnvironmentValue('DB_TEST_DATABASE');
        $username = self::requiredEnvironmentValue('DB_TEST_USER');
        $password = self::requiredEnvironmentValue('DB_TEST_PASS');

        if (!str_ends_with($database, '_test')) {
            throw new \RuntimeException('DB_TEST_DATABASE must end with _test.');
        }

        $pdo = new \PDO("mysql:host={$host};port={$port};charset=utf8mb4", $username, $password);
        $pdo->setAttribute(\PDO::ATTR_ERRMODE, \PDO::ERRMODE_EXCEPTION);
        $pdo->exec("CREATE DATABASE IF NOT EXISTS `{$database}` CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci");

        $capsule = new Capsule();
        $capsule->addConnection([
            'driver' => 'mysql',
            'host' => $host,
            'port' => $port,
            'database' => $database,
            'username' => $username,
            'password' => $password,
            'charset' => 'utf8mb4',
            'collation' => 'utf8mb4_unicode_ci',
            'prefix' => '',
        ]);
        $capsule->setAsGlobal();
        $capsule->bootEloquent();

        self::$databaseBooted = true;
    }

    private static function requiredEnvironmentValue(string $key): string
    {
        $value = $_ENV[$key] ?? getenv($key);

        if (!is_string($value) || $value === '') {
            throw new \RuntimeException("Missing required environment variable: {$key}");
        }

        return $value;
    }

    protected function setUp(): void
    {
        parent::setUp();

        Capsule::schema()->dropIfExists('cats');
        Capsule::schema()->dropIfExists('users');
        Capsule::schema()->create('users', function (Blueprint $table): void {
            $table->id();
            $table->string('email')->unique();
            $table->string('password');
            $table->string('remember_token')->nullable();
            $table->unsignedInteger('role_id')->default(1);
            $table->timestamps();
        });
        Capsule::schema()->create('cats', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
            $table->string('name', 100);
            $table->string('lang', 50);
            $table->timestamps();
        });

        if (session_status() === PHP_SESSION_ACTIVE) {
            session_destroy();
        }

        $_SESSION = [];
        $_COOKIE = [];
    }

    protected function tearDown(): void
    {
        $_SESSION = [];
        $_COOKIE = [];

        if (session_status() === PHP_SESSION_ACTIVE) {
            session_destroy();
        }

        parent::tearDown();
    }
}
