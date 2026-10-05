<?php

declare(strict_types=1);

namespace Simpledynamic\Services\Routing;

use ReflectionClass;
use Simpledynamic\Services\Filtration\Filter;
use Simpledynamic\Services\Filtration\FilterParam;
use Simpledynamic\Services\Filtration\Sanitization\SanitizationFilter;
use Simpledynamic\Services\Reflection\ClassReflectionBuilder;

/**
 * Разрешатель внешних параметров
 */
final class InputResolver
{
    public function __construct(
        private readonly Filter $filter = new Filter(),
    ) {}

    /**
     * Разрешить внешние параметры по названиям класса и метода
     *
     * @param class-string $className Название класса
     * @param string $methodName Название метода
     * @return array<string, non-empty-array<string, mixed>> Список внешних параметров по типам
     * @throws \ReflectionException
     */
    public function applyAttributeTo(string $className, string $methodName): array
    {
        if (!class_exists($className) || !method_exists($className, $methodName)) {
            return [];
        }

        $attributes = new ClassReflectionBuilder(
            reflectionClass: new ReflectionClass($className),
        )->readAttributes(memberName: $methodName, attributesNames: [
            'member' => [
                FilterParam::class,
            ],
        ]);

        return $this->filter(array_values(array_filter(
            $attributes['member'][FilterParam::class] ?? [],
            static fn(object $filterParam): bool => $filterParam instanceof FilterParam,
        )));
    }

    /**
     * Отфильтровать список фильтруемых параметров
     *
     * @param list<FilterParam> $filterParams Список фильтруемых параметров
     * @return array<string, non-empty-array<string, mixed>> Список внешних параметров по типам
     */
    public function filter(array $filterParams): array
    {
        if ($filterParams === []) {
            return [];
        }

        $params = [];

        foreach ($filterParams as $filterParam) {
            $varName = $filterParam->getVarName();

            if ($varName === null || $varName === '') {
                continue;
            }

            $params[$filterParam->getType()->name][$varName] = $this->getValue($filterParam);
        }

        return array_filter($params);
    }

    /**
     * Получить метод запроса
     */
    public function getRequestMethod(): ?string
    {
        $requestMethod = $this->filter->inputVarValue(arg: new FilterParam(
            type: InputType::SERVER,
            varName: 'REQUEST_METHOD',
            filter: SanitizationFilter::FULL_SPECIAL_CHARS,
        ));

        return is_string($requestMethod) ? $requestMethod : null;
    }

    /**
     * Получить значение фильтруемого параметра
     */
    private function getValue(FilterParam $filterParam): mixed
    {
        $type = $filterParam->getType();
        $varName = $filterParam->getVarName();

        if ($type instanceof InputType) {
            return $this->filter->inputVarValue(arg: $filterParam);
        }

        if ($varName === null) {
            return null;
        }

        return match ($type) {
            GlobalArray::FILES => array_key_exists($varName, $_FILES)
                ? $this->filter->varValue(value: $_FILES[$varName], arg: $filterParam)
                : null,
            GlobalArray::SESSION => array_key_exists($varName, $_SESSION)
                && (is_array($_SESSION[$varName]) || is_scalar($_SESSION[$varName]))
                    ? $this->filter->varValue(value: $_SESSION[$varName], arg: $filterParam)
                    : null,
        };
    }
}
