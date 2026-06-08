<?php


declare(strict_types=1);

namespace Simpledynamic\Services\CLI;

use Attribute;

/**
 * Опиция консольной команды
 */
#[Attribute(Attribute::TARGET_CLASS | Attribute::IS_REPEATABLE)]
final class Option
{
    /**
     * @psalm-suppress PossiblyUnusedMethod
     */
    public function __construct(
        public OptionType $type    = OptionType::FLAG,
        public string $short       = '',
        public string $long        = '',
        public string $description = '',
    ) {
    }
}
