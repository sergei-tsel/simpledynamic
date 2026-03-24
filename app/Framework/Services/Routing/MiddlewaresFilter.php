<?php

declare(strict_types=1);

namespace App\Framework\Services\Routing;

use App\Framework\Services\Reflection\ReflectionAttributeManager;

/**
 * Фильтр мидлваров
 */
class MiddlewaresFilter
{
    public function __construct(
        private array $names = [],
    ) {
    }

    /**
     * Прочитать атрибуты
     */
    public function readAttributes(object|string $class, ?string $methodName = null): MiddlewaresFilter
    {
        $methodAttributes = new ReflectionAttributeManager()->read(class: $class, memberName: $methodName, attributeName: Middleware::class);
        $classAttributes = new ReflectionAttributeManager()->read(class: $class, attributeName: Middleware::class);
        $attributes = array_merge($methodAttributes, $classAttributes);

        foreach ($attributes as $attribute) {
            /**
             * @var Middleware $attribute
             */
            $this->names[] = $attribute->getName();
        }

        return $this;
    }

    /**
     * Получить отфильтрованные данные
     */
    public function getFilteredData(array $names = []): array
    {
        $this->names = array_merge($this->names, $names);

        $middlewares = [];

        foreach ($this->names as $name) {
            if (method_exists($name, 'handle')) {
                $middlewares[] = $name;
            }
        }

        return $middlewares;
    }
}
