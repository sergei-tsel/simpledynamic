<?php

declare(strict_types=1);

namespace Test\App\Console\Migration;

use Simpledynamic\Integrations\Eloquent\MigrationRepository;
use Simpledynamic\Services\CLI\Command;

/**
 * Команад накатывающая новые миграции
 * migrate:run
 */
class Run extends Command
{
    #[\Override]
    protected static string $description = 'Накатывает новые миграции.';

    public function handle(MigrationRepository $repository): void
    {
        $repository->run();
    }
}
