<?php

declare(strict_types=1);

namespace Simpledynamic\Services\Filtration;

use Closure;
use Simpledynamic\Services\Routing\InputType;

/**
 * Фильтр
 *
 * Получает значения внешних переменных и произвольных значений, применяя к ним
 * фильтр, описанный аргументом фильтра
 */
final class Filter
{
    /**
     * Фильтр внешних переменных
     */
    private readonly InputFilter $inputFilter;

    public function __construct(
        private readonly InputGlobals $inputGlobals = new InputGlobals(),
        private readonly ValueFilter $valueFilter = new ValueFilter(),
        ?InputFilter $inputFilter = null,
    ) {
        $this->inputFilter = $inputFilter ?? new InputFilter(
            inputGlobals: $this->inputGlobals,
            valueFilter: $this->valueFilter,
        );
    }

    /**
     * Проверить, что внешняя переменная существует
     *
     * @param InputType $type Тип внешних переменных
     * @param string $name Название внешней переменной
     */
    public function inputVarExists(InputType $type, string $name): bool
    {
        return $this->inputGlobals->inputVarExists(type: $type, name: $name);
    }

    /**
     * Получить внешнюю переменную и отфильтровать её при необходимости
     *
     * Значение читается из исходных данных запроса. Если переменной там нет, но она
     * присутствует в глобальном массиве (например, в CLI-режиме), значение читается
     * из глобального массива
     *
     * @param FilterArgument $arg Аргумент фильтра
     * @return array<array-key, mixed>|string|float|int|bool|null
     */
    public function inputVarValue(FilterArgument $arg): array|string|float|int|bool|null
    {
        $type = $arg->getInputType();
        $name = $arg->getVarName();

        if ($type === null || $name === null) {
            return null;
        }

        return $this->inputVarExists(type: $type, name: $name)
            ? $this->originalVarValue(type: $type, name: $name, arg: $arg)
            : $this->globalVarValue(type: $type, name: $name, arg: $arg);
    }

    /**
     * Отфильтровать значение переменной при необходимости
     *
     * @param array<array-key, mixed>|string|float|int|bool|null $value Значение переменной
     * @param FilterArgument $arg Аргумент фильтра
     * @return array<array-key, mixed>|string|float|int|bool|null
     */
    public function varValue(
        array|string|float|int|bool|null $value,
        FilterArgument $arg,
    ): array|string|float|int|bool|null {
        return $this->valueFilter->value(value: $value, arg: $arg);
    }

    /**
     * Получить массив внешних переменных и отфильтровать их при необходимости
     *
     * Все аргументы читаются из типа внешних переменных $type
     *
     * @param InputType $type Тип внешних переменных
     * @param array<string, FilterArgument> $args Аргументы фильтра по названиям переменных
     * @param bool $addEmpty Добавлять отсутствующие переменные с пустым значением
     * @return array<array-key, mixed>|false|null Значения по названиям переменных
     */
    public function inputVars(InputType $type, array $args, bool $addEmpty = true): array|false|null
    {
        return $this->inputFilter->inputVars(type: $type, args: $args, addEmpty: $addEmpty);
    }

    /**
     * Отфильтровать массив переменных при необходимости
     *
     * @param array<array-key, mixed> $vars Значения по названиям переменных
     * @param array<string, FilterArgument> $args Аргументы фильтра по названиям переменных
     * @param bool $addEmpty Добавлять отсутствующие переменные с пустым значением
     * @return array<array-key, mixed> Отфильтрованные значения по названиям переменных
     */
    public function vars(array $vars, array $args, bool $addEmpty = true): array
    {
        return $this->inputFilter->values(vars: $vars, args: $args, addEmpty: $addEmpty);
    }

    /**
     * Получить и отфильтровать внешнюю переменную из исходных данных запроса
     *
     * @param InputType $type Тип внешних переменных
     * @param string $name Название внешней переменной
     * @param FilterArgument $arg Аргумент фильтра
     * @return array<array-key, mixed>|string|float|int|bool|null
     */
    private function originalVarValue(
        InputType $type,
        string $name,
        FilterArgument $arg,
    ): array|string|float|int|bool|null {
        $options = $arg->getOptions();

        if ($options === null) {
            return null;
        }

        if ($options instanceof Closure) {
            return $this->valueFilter->value(value: filter_input(type: $type->value, var_name: $name), arg: $arg);
        }

        return $this->valueFilter->normalize(filter_input(
            type: $type->value,
            var_name: $name,
            filter: $arg->getFilter()->value,
            options: $options,
        ));
    }

    /**
     * Получить и отфильтровать внешнюю переменную из глобального массива
     *
     * @param InputType $type Тип внешних переменных
     * @param string $name Название внешней переменной
     * @param FilterArgument $arg Аргумент фильтра
     * @return array<array-key, mixed>|string|float|int|bool|null
     */
    private function globalVarValue(
        InputType $type,
        string $name,
        FilterArgument $arg,
    ): array|string|float|int|bool|null {
        $value = $this->inputGlobals->inputVarValue(type: $type, name: $name);

        return $value === null ? null : $this->valueFilter->value(value: $value, arg: $arg);
    }
}
