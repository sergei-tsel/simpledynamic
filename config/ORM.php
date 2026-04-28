<?php

declare(strict_types=1);

namespace config;

use Illuminate\Database\Capsule\Manager;

/**
 * Конфигурация подключения к базе данных для ORM
 *
 * @api
 * @psalm-suppress ClassCanBeFinal
 */
class ORM extends Config
{
    /**
     * @var array<string, array<array-key, mixed>|scalar|null>
     * @psalm-suppress InvalidAttribute
     */
    #[\Override]
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
    /**
     * @psalm-suppress InvalidAttribute
     */
    #[\Override]
    protected static string $filename            = '';

    /**
     * @var string[]
     */
    protected static array $migrationDirectories = [
        __DIR__ . '/../framework/Integrations/Eloquent/Migrations',
    ];

    public static function getMigrationDirectories(): array
    {
        return self::$migrationDirectories;
    }

    /**
     * Создать конфигурацию подключения к базе данных для Eloquent
     */
    public static function createEloquent(): Manager
    {
        $eloquent = self::getConfig();

        $manager = new Manager();

        $manager->addConnection($eloquent);

        $manager->bootEloquent();

        return $manager;
    }
}
