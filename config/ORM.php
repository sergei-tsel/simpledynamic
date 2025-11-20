<?php

declare(strict_types=1);

namespace config;

use Illuminate\Database\Capsule\Manager as Capsule;

/**
 * Конфигурация подключения к базе данных для ORM
 */
class ORM extends Config
{
    protected static array  $local    = [
        'driver'    => 'mysql',
        'host'      => 'localhost',
        'database'  => 'database',
        'username'  => 'root',
        'password'  => 'password',
        'charset'   => 'utf8',
        'collation' => 'utf8_unicode_ci',
        'prefix'    => '',
    ];
    protected static string $filename = '';

    /**
     * Создать конфигурацию подключения к базе данных для Eloquent
     */
    public static function createEloquentConfig(): void
    {
        $eloquent = self::getConfig();

        $capsule = new Capsule;

        $capsule->addConnection($eloquent);

        $capsule->bootEloquent();
    }
}