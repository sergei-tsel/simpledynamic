<?php

declare(strict_types=1);

namespace Sympledynamic\Services\Reflection;

use ReflectionAttribute;
use ReflectionClass;
use ReflectionClassConstant;
use ReflectionMethod;
use ReflectionParameter;
use ReflectionProperty;

/**
 * Инстанцируемые атрибуты рефлексии
 */
trait InstanceableReflectionAttributes
{
    /**
     * Получить экземпляры множества атрибутов
     *
     * @return array<string, object[]>
     */
    protected function instanceManyAttributes(
        ReflectionClass|ReflectionClassConstant|ReflectionMethod|ReflectionParameter|ReflectionProperty $reflectionEntity,
        array                                                                                           $attributesNames   = [],
        bool                                                                                            $isInstanceOf      = false
    ): array {
        if ($attributesNames === []) {
            return [];
        }

        if (count($attributesNames) === 1) {
            return [
                $attributesNames[0] => $this->instanceAttributes(
                    reflectionEntity: $reflectionEntity,
                    attributeName: $attributesNames[0],
                    isInstanceOf: $isInstanceOf,
                ),
            ];
        }

        $attributes = $this->instanceAttributes(reflectionEntity: $reflectionEntity, isInstanceOf: $isInstanceOf);

        $filteredAttributes = [];

        foreach ($attributes as $attribute) {
            if (in_array($attribute::class, $attributesNames)) {
                $filteredAttributes[$attribute::class][] = $attribute;
            }
        }

        return $filteredAttributes;
    }

    /**
     * @template T
     *
     * Получить экземпляры атрибутов
     *
     * @param class-string<T>|null $attributeName
     * @return object[]
     */
    protected function instanceAttributes(
        ReflectionClass|ReflectionClassConstant|ReflectionMethod|ReflectionParameter|ReflectionProperty $reflectionEntity,
        ?string                                                                                         $attributeName     = null,
        bool                                                                                            $isInstanceOf      = false
    ): array {
        $reflectionAttributes = $reflectionEntity->getAttributes(
            name: $attributeName ?: null,
            flags: $isInstanceOf ? ReflectionAttribute::IS_INSTANCEOF : 0,
        );

        if ($reflectionAttributes === []) {
            return [];
        }

        return array_map(fn (ReflectionAttribute $attribute): object => $attribute->newInstance(), $reflectionAttributes);
    }
}
