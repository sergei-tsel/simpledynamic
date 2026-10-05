<?php

declare(strict_types=1);

namespace Test\Config;

use Simpledynamic\Services\Configuration\Config;

/**
 * Конфигурация приложения
 */
class App extends Config
{
    /**
     * @var array<string, array<array-key, mixed>|scalar|null>
     */
    #[\Override]
    protected static array $local = [
        'locale' => 'en',
        'commands' => [
            'greet' => \Test\App\Console\Greeting\Greet::class,
            'migrate:create-db' => \Test\App\Console\Migration\CreateDatabase::class,
            'migrate:rollback' => \Test\App\Console\Migration\Rollback::class,
            'migrate:run' => \Test\App\Console\Migration\Run::class,
        ],
        'providers' => [
            \Test\Framework\Providers\AppServiceProvider::class,
        ],
        'twig_extensions' => [],
        'twig_path' => __DIR__ . '/../public/twig',
    ];

    #[\Override]
    protected static string $filename = '';
}
