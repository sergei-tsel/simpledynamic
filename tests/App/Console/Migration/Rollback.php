<?php

declare(strict_types=1);

namespace Test\App\Console\Migration;

use Simpledynamic\Integrations\Eloquent\MigrationRepository;
use Simpledynamic\Services\CLI\Command;
use Simpledynamic\Services\CLI\Option;
use Simpledynamic\Services\CLI\OptionType;

/**
 * Команада, откатывающая миграции
 * migrate:rollback {--batch} {--last-count::}
 */
#[Option(
    type: OptionType::FLAG,
    short: '-b',
    long: '--batch',
    description: 'Если не передан, то откатываются только миграции из последней партии.',
)]
#[Option(
    type: OptionType::OPTIONAL,
    short: '-c',
    long: '--last-count',
    description: 'Количество последних накатанных миграций. Если не передан, откатываются все возможные миграции.',
)]
class Rollback extends Command
{
    #[\Override]
    protected static string $description = 'Откатывает миграции.';

    public function handle(MigrationRepository $repository): void
    {
        /** @var bool|null $hasMaxBatch*/
        $hasMaxBatch = $this->getArgument('batch');
        /** @var string|int|null $lastCount */
        $lastCount = $this->getArgument('last-count');

        $lastCount = is_string($lastCount) && $lastCount !== '' ? (int) $lastCount : null;

        match (true) {
            $hasMaxBatch === true => $repository->rollback(),
            $lastCount !== null => $repository->rollback(lastCount: $lastCount),
            default => $repository->rollback(),
        };
    }
}
