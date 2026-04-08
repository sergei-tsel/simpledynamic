<?php

declare(strict_types=1);

namespace Framework\Services\Reflection;

use ReflectionClass;
use ReflectionClassConstant;
use ReflectionException;
use ReflectionMethod;
use ReflectionProperty;

/**
 * Сервис для управления классом с помощью рефлексии
 */
class ClassReflectionManager
{
    use InstanceableReflectionAttributes;

    /**
     * Прочитать атрибуты
     *
     * @return array<string, object[][]|array<string, object[][]>
     */
    public function readAttributes(object|string $class, ?string $memberName = null, array $attributesNames = [], array $areInstanceOf = []): array
    {
        $reflectionClass = $this->create($class);

        if ($reflectionClass === null) {
            return [];
        }

        $attributes = [
            'class' => $this->instanceManyAttributes(
                reflectionEntity: $reflectionClass,
                attributesNames: $attributesNames['class'] ?? [],
                isInstanceOf: !empty($areInstanceOf['class']),
            ),
        ];

        if ($memberName === null) {
            return $attributes;
        }

        $classMemberAttributes = $this->readMemberAttributes(
            class: $reflectionClass,
            memberName: $memberName,
            attributesNames: [
                'member' => $attributesNames['member'] ?? [],
                'params' => $attributesNames['params'] ?? [],
            ],
            areInstanceOf: [
                'member' => $areInstanceOf['member'] ?? [],
                'params' => $areInstanceOf['params'] ?? [],
            ],
        );

        $attributes['member'] = $classMemberAttributes['member'] ?? [];
        $attributes['params'] = $classMemberAttributes['params'] ?? [];

        return $attributes;
    }

    /**
     * Прочитать атрибуты члена класса
     *
     * @return array<string, object[][]|array<string, object[][]>
     */
    public function readMemberAttributes(object|string $class, ?string $memberName = null, array $attributesNames = [], array $areInstanceOf = []): array
    {
        if (!$class instanceof ReflectionClass) {
            $class = $this->create($class);

            if ($class === null) {
                return [];
            }
        }

        $classMember = $this->createMember(class: $class, memberName: $memberName);

        if ($classMember === null) {
            return [];
        }

        $attributes['member'] = $this->instanceManyAttributes(
            reflectionEntity: $classMember,
            attributesNames: $attributesNames['member'] ?? [],
            isInstanceOf: !empty($areInstanceOf['member']),
        );

        if ($classMember instanceof ReflectionMethod && array_key_exists('params', $attributesNames)) {
            foreach ($classMember->getParameters() as $reflectionParam) {
                $attributes['params'][$reflectionParam->getName()] = $this->instanceManyAttributes(
                    reflectionEntity: $reflectionParam,
                    attributesNames: $attributesNames['params'] ?? [],
                    isInstanceOf: !empty($areInstanceOf['params']),
                );
            }
        }

        return $attributes;
    }

    /**
     * Создать объект рефлексии класса
     */
    private function create(object|string $class): ?ReflectionClass
    {
        try {
            return new ReflectionClass($class);
        } catch (ReflectionException) {
            return null;
        }
    }

    /**
     * Создать объект рефлексии члена класса
     */
    private function createMember(object|string $class, string $memberName): ReflectionClass|ReflectionClassConstant|ReflectionMethod|ReflectionProperty|null
    {
        if (!$class instanceof ReflectionClass) {
            $class = $this->create($class);
        }

        try {
            return match (true) {
                $memberName === '__construct'                  => $class->getConstructor(),
                $class->hasMethod($memberName)                 => $class->getMethod($memberName),
                $class->hasProperty($memberName)               => $class->getProperty($memberName),
                $class->hasConstant($memberName)               => $class->getReflectionConstant($memberName) ?: null,
                $class->isSubclassOf($memberName),
                in_array($memberName, $class->getTraitNames()) => $this->create($class),
                default                                        => null,
            };
        } catch (ReflectionException) {
            return null;
        }
    }
}
