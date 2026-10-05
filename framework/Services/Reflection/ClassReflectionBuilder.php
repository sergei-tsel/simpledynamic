<?php

declare(strict_types=1);

namespace Simpledynamic\Services\Reflection;

use ReflectionClass;
use ReflectionClassConstant;
use ReflectionMethod;
use ReflectionProperty;
use Simpledynamic\Base\Model\BuilderInterface;

/**
 * Билдер для работы с классом через рефлексию
 */
final class ClassReflectionBuilder implements BuilderInterface
{
    use InstanceableReflectionAttributes;

    /**
     * @var array<string, ReflectionMethod|ReflectionProperty|ReflectionClassConstant>
     */
    private array $members = [];

    public function __construct(
        private readonly ReflectionClass $reflectionClass,
    ) {}

    /**
     *  Получить объект рефлексии класса
     */
    public function getReflectionClass(): ReflectionClass
    {
        return $this->reflectionClass;
    }

    /**
     * Получить объект рефлексии метода
     *
     * @throws \ReflectionException
     */
    public function getReflectionMethod(?string $name = null): ?ReflectionMethod
    {
        if ($name === null) {
            return $this->reflectionClass->getConstructor();
        }

        if (!$this->reflectionClass->hasMethod($name)) {
            return null;
        }

        return $this->reflectionClass->getMethod($name);
    }

    /**
     * Прочитать атрибуты
     *
     * @param array<string, list<class-string>> $attributesNames
     * @param array<string, list<class-string>> $areInstanceOf
     * @return array{class: array<class-string, list<object>>, member?: array<class-string, list<object>>, params?: array<string, array<class-string, list<object>>>}
     */
    public function readAttributes(
        ?string $memberName = null,
        array $attributesNames = [],
        array $areInstanceOf = [],
    ): array {
        $attributes = [
            'class' => $this->instanceManyAttributes(
                reflectionEntity: $this->reflectionClass,
                attributesNames: $attributesNames['class'] ?? [],
                attributeFlags: ($areInstanceOf['class'] ?? []) !== [] ? \ReflectionAttribute::IS_INSTANCEOF : 0,
            ),
        ];

        if ($memberName === null) {
            return $attributes;
        }

        $classMember = $this->getMember($memberName);

        if ($classMember === null) {
            $attributes['member'] = [];
            $attributes['params'] = [];

            return $attributes;
        }

        $memberAttributes = $this->readMemberAttributes(
            classMember: $classMember,
            memberAttributesNames: $attributesNames['member'] ?? [],
            memberAreInstanceOf: $areInstanceOf['member'] ?? [],
        );

        $attributes['member'] = $memberAttributes['member'];
        $attributes['params'] = $memberAttributes['params'];

        return $attributes;
    }

    /**
     * Получить конструктор класса
     */
    public function getConstructor(): ?ReflectionMethod
    {
        return $this->reflectionClass->getConstructor();
    }

    /**
     * Создать экземпляр класса с аргументами
     *
     * @param array<int, mixed> $args
     * @throws \ReflectionException
     */
    public function newInstanceArgs(array $args = []): object
    {
        $instance = $this->reflectionClass->newInstanceArgs($args);

        if ($instance === null) {
            throw new \ReflectionException('Не удалось создать экземпляр класса');
        }

        return $instance;
    }

    /**
     * Получить члена класса по имени с кэшированием
     *
     * @return ReflectionMethod|ReflectionProperty|ReflectionClassConstant|null
     */
    private function getMember(string $memberName): ReflectionMethod|ReflectionProperty|ReflectionClassConstant|null
    {
        if (array_key_exists($memberName, $this->members)) {
            return $this->members[$memberName];
        }

        $member = $this->resolveMember($this->reflectionClass, $memberName);

        if ($member === null) {
            return null;
        }

        $this->members[$memberName] = $member;

        return $member;
    }
}
