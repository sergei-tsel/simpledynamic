<?php

declare(strict_types=1);

namespace Simpledynamic\Services\Routing;

use Attribute;
use Simpledynamic\Base\Controller\MiddlewareInterface;

/**
 * Фильтруемый мидлвар
 */
#[Attribute(Attribute::TARGET_CLASS | Attribute::TARGET_METHOD | Attribute::IS_REPEATABLE)]
readonly class Middleware implements MiddlewareInterface
{
    public function __construct(
        /** @var class-string */
        private string $name,
    ) {}

    /**
     * Получить имя
     *
     * @return class-string
     */
    public function getName(): string
    {
        return $this->name;
    }

    /**
     * Обработать
     *
     * @param array ...$args Параметры
     */
    #[\Override]
    public function handle(array ...$args): mixed
    {
        if (!class_exists($this->name) || !method_exists($this->name, 'handle')) {
            return null;
        }

        return $this->name::handle(...$args);
    }
}
