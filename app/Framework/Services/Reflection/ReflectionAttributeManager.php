<?php

declare(strict_types=1);

namespace App\Framework\Services\Reflection;

use Attribute;
use ReflectionAttribute;
use ReflectionClass;

/**
 * Сервис для управления атрибутами рефлексии
 */
class ReflectionAttributeManager
{
    /**
     * Прочитать атрибуты
     *
     * @return Attribute[]
     * @throws \ReflectionException
     */
    public function read(object|string $class, ?string $memberName = null, ?string $attributeName = null, bool $isInstanceOf = false): array
    {
        if ($memberName === null) {
            return new ReflectionAttributeManager()->readClass(class: $class, attributeName: $attributeName, isInstanceOf: $isInstanceOf);
        } elseif ($memberName === '__construct') {
            return new ReflectionAttributeManager()->readConstructor(class: $class, attributeName: $attributeName, isInstanceOf: $isInstanceOf);
        } else {
            return new ReflectionAttributeManager()->readClassMember(class: $class, memberName: $memberName, attributeName: $attributeName, isInstanceOf: $isInstanceOf);
        }
    }

    /**
     * Прочитать атрибуты класса
     *
     * @return Attribute[]
     * @throws \ReflectionException
     */
    public function readClass(object|string $class, ?string $attributeName = null, bool $isInstanceOf = false): array
    {
        $reflectionClass = new ReflectionClass($class);

        $attributes = $reflectionClass->getAttributes(name: $attributeName, flags: $isInstanceOf ? ReflectionAttribute::IS_INSTANCEOF : 0);

        return $this->getAttributesInstances($attributes);
    }

    /**
     * Прочитать атрибуты конструктора класса
     *
     * @return Attribute[]
     * @throws \ReflectionException
     */
    public function readConstructor(object|string $class, ?string $attributeName = null, bool $isInstanceOf = false): array
    {
        $reflectionClass = new ReflectionClass($class);

        $reflectionMethod = $reflectionClass->getConstructor();

        if ($reflectionMethod === null) {
            return [];
        }

        $attributes = $reflectionMethod->getAttributes(name: $attributeName, flags: $isInstanceOf ? ReflectionAttribute::IS_INSTANCEOF : 0);

        return $this->getAttributesInstances($attributes);
    }

    /**
     * Прочитать атрибуты члена класса
     *
     * @return Attribute[]
     * @throws \ReflectionException
     */
    public function readClassMember(object|string $class, string $memberName, ?string $attributeName = null, bool $isInstanceOf = false): array
    {
        $reflectionClass = new ReflectionClass($class);

        $reflectionEntity = match (true) {
            $reflectionClass->hasMethod($memberName)                 => $reflectionClass->getMethod($memberName),
            $reflectionClass->hasProperty($memberName)               => $reflectionClass->getProperty($memberName),
            $reflectionClass->hasConstant($memberName)               => $reflectionClass->getReflectionConstant($memberName),
            $reflectionClass->isSubclassOf($memberName),
            in_array($memberName, $reflectionClass->getTraitNames()) => new ReflectionClass($memberName),
        };

        $attributes = $reflectionEntity->getAttributes(name: $attributeName, flags: $isInstanceOf ? ReflectionAttribute::IS_INSTANCEOF : 0);

        return $this->getAttributesInstances($attributes);
    }

    /**
     * Преобразовать массив атрибутов рефлексии в массив атрибутов
     *
     * @param ReflectionAttribute[] $attributes
     * @return Attribute[]
     */
    protected function getAttributesInstances(array $attributes): array
    {
        return array_map(fn(\ReflectionAttribute $attribute): object => $attribute->newInstance(), $attributes);
    }
}
