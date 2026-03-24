<?php

declare(strict_types=1);

namespace App\Framework\Services\Routing;

use Attribute;

#[Attribute(Attribute::TARGET_CLASS|Attribute::TARGET_METHOD)]
/**
 * Фильтруемый мидлвар
 */
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
