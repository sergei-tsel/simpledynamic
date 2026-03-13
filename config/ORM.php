<?php

declare(strict_types=1);

namespace config;

use Illuminate\Database\Capsule\Manager;
use Illuminate\Database\DatabaseManager;

/**
 * Конфигурация подключения к базе данных для ORM
 */
class ORM extends Config
{
    protected static array  $local               = [
        'driver'    => 'mysql',
        'host'      => 'localhost',
        'database'  => 'database',
        'username'  => 'root',
        'password'  => 'password',
        'charset'   => 'utf8',
        'collation' => 'utf8_unicode_ci',
        'prefix'    => '',
    ];
    protected static string $filename            = '';

    protected static array $migrationDirectories = [
        __DIR__ . '/../app/Framework/Services/DB/Eloquent/Migrations',
    ];

    public static function getMigrationDirectories(): array
    {
        return self::$migrationDirectories;
    }

    /**
     * Создать конфигурацию подключения к базе данных для Eloquent
     */
    public static function createEloquent(): DatabaseManager
    {
        $eloquent = self::getConfig();

        $manager = new Manager();

        $manager->addConnection($eloquent);

        $manager->bootEloquent();

        return $manager->getDatabaseManager();
    }
}
