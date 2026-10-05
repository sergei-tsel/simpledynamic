<?php

declare(strict_types=1);

namespace Test\Config;

use Illuminate\Database\Capsule\Manager;
use Simpledynamic\Services\Configuration\Config;

/**
 * Конфигурация ORM
 */
class ORM extends Config
{
    /**
     * @var array<string, array<array-key, mixed>|scalar|null>
     */
    #[\Override]
    protected static array $local = [
        'driver' => 'pgsql',
        'host' => 'localhost',
        'database' => 'tablemap',
        'username' => 'postgres',
        'password' => '',
        'charset' => 'utf8',
        'collation' => 'utf8_unicode_ci',
        'prefix' => '',
    ];

    #[\Override]
    protected static string $filename = '';

    /**
     * @var string[]
     */
    protected static array $migrationDirectories = [
        __DIR__ . '/../App/Storage/Migration',
        __DIR__ . '/../App/Storage/User',
        __DIR__ . '/../App/Storage/Page',
    ];

    /**
     * @return list<string> Список каталогов миграций
     */
    public static function getMigrationDirectories(): array
    {
        return array_values(self::$migrationDirectories);
    }

    /**
     * Создать конфигурацию подключения к базе данных для Eloquent
     */
    public static function createEloquent(): Manager
    {
        $eloquent = self::getConfig();

        $password = getenv('DB_PASSWORD');

        if (is_string($password) && $password !== '') {
            $eloquent['password'] = $password;
        }

        $manager = new Manager();

        $manager->addConnection($eloquent);

        $manager->bootEloquent();

        return $manager;
    }
}
