<?php

declare(strict_types=1);

namespace App\Framework\Services\ParamsFiltration;

use App\Framework\Services\Routing\InputTypes;

class Filter
{
    /**
     * Получить переменную из суперглобального массива и отфильтровать её при необходимости
     */
    public function inputVarValue(FilterArgument $arg): mixed
    {
        return filter_input(type: $arg->getInputType()->value, var_name: $arg->getVarName(), filter: $arg->getFilterId(), options: $arg->getFlagOptions());
    }

    /**
     * Отфильтровать переменную при необходимости
     */
    public function varValue(string $value, FilterArgument $arg): mixed
    {
        return filter_var(value: $value, filter: $arg->getFilterId(), options: $arg->getFlagOptions());
    }

    /**
     * Получить массив переменных из суперглобального массива и отфильтровать их при необходимости
     *
     * @param FilterArgument[] $args
     */
    public function inputVars(InputTypes $type, array $args, bool $addEmpty = true): array|false|null
    {
        if ($args === []) {
            return [];
        }

        if (count($args) === 1) {
            return [
                array_key_first($args) => $this->inputVarValue(arg: array_first($args)),
            ];
        }

        $options = array_map(fn (FilterArgument $arg): array => $arg->getFilterFlagOptions(), $args);

        return filter_input_array(type: $type->value, options: $options, add_empty: $addEmpty);
    }

    /**
     * Отфильтровать массив переменных при необходимости
     *
     * @param FilterArgument[] $args
     */
    public function vars(array $vars, array $args, bool $addEmpty = true): array|false|null
    {
        if ($vars === []) {
            return [];
        }

        if ($args === []) {
            return $vars;
        }

        if (count($vars) === 1) {
            return [
                array_key_first($args) => $this->varValue(value: array_first($vars), arg: array_first($args)),
            ];
        }

        $options = array_map(fn (FilterArgument $arg): array => $arg->getFilterFlagOptions(), $args);

        return filter_var_array(array: $vars, options: $options, add_empty: $addEmpty);
    }
}
