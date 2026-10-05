<?php

declare(strict_types=1);

namespace Simpledynamic\Services\Reflection;

use ReflectionAttribute;
use ReflectionClass;
use ReflectionClassConstant;
use ReflectionMethod;
use ReflectionParameter;
use ReflectionProperty;
use Simpledynamic\Services\Logging\Logger;

/**
 * Инстанцируемые атрибуты рефлексии
 */
trait InstanceableReflectionAttributes
{
    /**
     * Разрешить члена класса по имени через рефлексию
     *
     * @return ReflectionMethod|ReflectionProperty|ReflectionClassConstant|null
     */
    protected function resolveMember(
        ReflectionClass $reflectionClass,
        string $memberName,
    ): ReflectionMethod|ReflectionProperty|ReflectionClassConstant|null {
        try {
            return match (true) {
                $memberName === '__construct' => $this->resolveConstructor($reflectionClass),
                $reflectionClass->hasMethod($memberName) => $reflectionClass->getMethod($memberName),
                $reflectionClass->hasProperty($memberName) => $reflectionClass->getProperty($memberName),
                $reflectionClass->hasConstant($memberName) => $this->resolveConstant($reflectionClass, $memberName),
                default => null,
            };
        } catch (\ReflectionException $exception) {
            // Члены класса проверяются на существование до обращения к ним,
            // поэтому исключение означает рассинхронизацию проверки и доступа
            Logger::report($exception);

            return null;
        }
    }

    private function resolveConstructor(ReflectionClass $reflectionClass): ?ReflectionMethod
    {
        return $reflectionClass->getConstructor();
    }

    private function resolveConstant(ReflectionClass $reflectionClass, string $name): ?ReflectionClassConstant
    {
        $constant = $reflectionClass->getReflectionConstant($name);

        return $constant !== false ? $constant : null;
    }

    /**
     * Прочитать атрибуты члена класса
     *
     * @param list<class-string> $memberAttributesNames
     * @param list<class-string> $memberAreInstanceOf
     * @return array{member: array<class-string, list<object>>, params: array<string, array<class-string, list<object>>>}
     */
    protected function readMemberAttributes(
        ReflectionMethod|ReflectionProperty|ReflectionClassConstant $classMember,
        array $memberAttributesNames = [],
        array $memberAreInstanceOf = [],
    ): array {
        /** @var array<string, array<class-string, list<object>>> $params */
        $params = [];

        if ($classMember instanceof ReflectionMethod) {
            foreach ($classMember->getParameters() as $reflectionParam) {
                $params[$reflectionParam->getName()] = $this->instanceManyAttributes(
                    reflectionEntity: $reflectionParam,
                    attributesNames: [],
                    attributeFlags: 0,
                );
            }
        }

        return [
            'member' => $this->instanceManyAttributes(
                reflectionEntity: $classMember,
                attributesNames: $memberAttributesNames,
                attributeFlags: $memberAreInstanceOf !== [] ? ReflectionAttribute::IS_INSTANCEOF : 0,
            ),
            'params' => $params,
        ];
    }

    /**
     * Получить экземпляры множества атрибутов
     *
     * @param array<int, class-string> $attributesNames
     * @param int $attributeFlags Флаги ReflectionAttribute
     * @return array<class-string, list<object>>
     */
    protected function instanceManyAttributes(
        ReflectionClass|ReflectionClassConstant|ReflectionMethod|ReflectionParameter|ReflectionProperty $reflectionEntity,
        array $attributesNames = [],
        int $attributeFlags = 0,
    ): array {
        if (count($attributesNames) === 1) {
            $attributeName = array_values($attributesNames)[0];

            return [
                $attributeName => $this->instanceAttributes(
                    reflectionEntity: $reflectionEntity,
                    attributeName: $attributeName,
                    attributeFlags: $attributeFlags,
                ),
            ];
        }

        /** @var array<int, object> $attributes */
        $attributes = $this->instanceAttributes(reflectionEntity: $reflectionEntity, attributeFlags: $attributeFlags);

        /** @var array<class-string, list<object>> $filteredAttributes */
        $filteredAttributes = [];

        foreach ($attributes as $attribute) {
            if ($attributesNames !== [] && !in_array($attribute::class, $attributesNames, true)) {
                continue;
            }

            $filteredAttributes[$attribute::class][] = $attribute;
        }

        return $filteredAttributes;
    }

    /**
     * Получить экземпляры атрибутов
     *
     * @param class-string|null $attributeName
     * @param int $attributeFlags Флаги ReflectionAttribute
     * @return list<object>
     */
    protected function instanceAttributes(
        ReflectionClass|ReflectionClassConstant|ReflectionMethod|ReflectionParameter|ReflectionProperty $reflectionEntity,
        ?string $attributeName = null,
        int $attributeFlags = 0,
    ): array {
        $reflectionAttributes = $reflectionEntity->getAttributes(name: $attributeName ?? null, flags: $attributeFlags);

        if ($reflectionAttributes === []) {
            return [];
        }

        return array_map(
            static fn(ReflectionAttribute $attribute): object => $attribute->newInstance(),
            $reflectionAttributes,
        );
    }
}
