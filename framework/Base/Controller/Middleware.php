<?php

declare(strict_types=1);

namespace Framework\Base\Controller;

use Attribute;

/**
 * Фильтруемый мидлвар
 */
#[Attribute(Attribute::TARGET_CLASS | Attribute::TARGET_METHOD | Attribute::IS_REPEATABLE)]
readonly class Middleware
{
    public function __construct(
        private string $name,
    ) {
    }

    /**
     * Получить имя
     */
    public function getName(): string
    {
        return $this->name;
    }
}
