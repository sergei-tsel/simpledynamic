<?php

declare(strict_types=1);

namespace Sympledynamic\Base\Controller;

use Attribute;

/**
 * Фильтруемый мидлвар
 *
 * @psalm-suppress ClassCanBeFinal
 */
#[Attribute(Attribute::TARGET_CLASS | Attribute::TARGET_METHOD | Attribute::IS_REPEATABLE)]
readonly class Middleware
{
    /**
     * @psalm-suppress PossiblyUnusedMethod
     */
    public function __construct(
        private string $name,
    ) {
    }

    /**
     * Получить имя
     *
     * @psalm-suppress PossiblyUnusedMethod
     */
    public function getName(): string
    {
        return $this->name;
    }
}
