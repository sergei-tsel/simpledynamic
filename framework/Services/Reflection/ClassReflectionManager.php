<?php

declare(strict_types=1);

namespace Simpledynamic\Services\Reflection;

use ReflectionClass;
use ReflectionClassConstant;
use ReflectionException;
use ReflectionMethod;
use ReflectionProperty;

/**
 * Сервис для управления классом с помощью рефлексии
 */
final class ClassReflectionManager
{
    use InstanceableReflectionAttributes;

    /**
     * Прочитать атрибуты
     *
     * @param object|class-string $class
     * @param array<string, array<int, class-string>|null> $attributesNames
     * @return array{
     *     class?: array<class-string, list<object>>,
     *     member?: array<class-string, list<object>>,
     *     params?: array<non-empty-string, array<class-string, list<object>>>
     * }
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
     * @param object|class-string $class
     * @param array<string, array<int, class-string>|null> $attributesNames
     * @return array{member?: array<class-string, list<object>>, params?: array<non-empty-string, array<class-string, list<object>>>}
     */
    public function readMemberAttributes(object|string $class, string $memberName, array $attributesNames = [], array $areInstanceOf = []): array
    {
        if (!$class instanceof ReflectionClass) {
            $class = $this->create($class);

            if ($class === null) {
                return [];
            }
        }

        /**
         * @var ReflectionClass $class
         */
        $classMember = $this->createMember(class: $class, memberName: $memberName);

        if ($classMember === null) {
            return [];
        }

        $attributes = [
            'member' => $this->instanceManyAttributes(
                reflectionEntity: $classMember,
                attributesNames: $attributesNames['member'] ?? [],
                isInstanceOf: !empty($areInstanceOf['member']),
            ),
        ];

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
     *
     * @param object|class-string $class
     * @return ReflectionClass|null
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
     *
     * @param object|class-string $class
     */
    private function createMember(object|string $class, string $memberName): ReflectionClass|ReflectionClassConstant|ReflectionMethod|ReflectionProperty|null
    {
        if (!$class instanceof ReflectionClass) {
            $class = $this->create($class);
        }

        try {
            /** @var ReflectionClass $class */
            return match (true) {
                $memberName === '__construct'                                   => $class->getConstructor(),
                $class->hasMethod($memberName)                                  => $class->getMethod($memberName),
                $class->hasProperty($memberName)                                => $class->getProperty($memberName),
                $class->hasConstant($memberName)                                => $class->getReflectionConstant($memberName) ?: null,
                class_exists($memberName) && $class->isSubclassOf($memberName),
                in_array($memberName, $class->getTraitNames())                  => $this->create($class),
                default                                                         => null,
            };
        } catch (ReflectionException) {
            return null;
        }
    }
}
