<?php

declare(strict_types=1);

namespace Simpledynamic\Integrations\Eloquent;

use Simpledynamic\Services\Configuration\ORM;

/**
 * Поиск файлов миграций в директориях
 *
 * Используется единообразно для прокатки и отката миграций:
 *   - для run вызывается callback, создающий миграцию
 *   - для rollback вызывается callback, удаляющий миграцию
 */
final class MigrationLocator
{
    private const string FILENAME_PATTERN = '/^(\d{4}_\d{2}_\d{2}_\d{6})_(.*)_table\.php$/';

    /** @var string[] */
    private array $directories = [];

    public function __construct()
    {
        $raw = ORM::getMigrationDirectories();

        /** @var mixed $directory */
        foreach ($raw as $directory) {
            if (!is_string($directory)) {
                continue;
            }

            $this->directories[] = $directory;
        }
    }

    /**
     * Перебрать все файлы миграций во всех директориях
     *
     * Callback получает путь к директории и имя миграции (без расширения).
     *
     * @param callable(string $directory, string $name): void $callback
     */
    public function each(callable $callback): void
    {
        foreach ($this->directories as $directory) {
            $this->scan($directory, $callback);
        }
    }

    /**
     * Просканировать директорию и передать callback каждую найденную миграцию
     *
     * @param callable(string $directory, string $name): void $callback
     */
    private function scan(string $directory, callable $callback): void
    {
        $files = scandir($directory);

        if (!is_array($files)) {
            return;
        }

        foreach ($files as $file) {
            if (!preg_match(self::FILENAME_PATTERN, $file)) {
                continue;
            }

            $callback($directory, basename($file, '.php'));
        }
    }
}
