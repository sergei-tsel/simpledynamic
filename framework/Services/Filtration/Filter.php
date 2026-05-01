<?php

declare(strict_types=1);

namespace Sympledynamic\Services\Filtration;

use Sympledynamic\Services\Routing\InputType;

final class Filter
{
    /**
     * Проверить, что внешняя переменная существует
     *
     * @psalm-suppress PossiblyUnusedMethod
     */
    public function inputVarExists(InputType $type, string $name): bool
    {
        return filter_has_var($type->value, $name);
    }

    /**
     * Получить внешнюю переменную и отфильтровать её при необходимости
     */
    public function inputVarValue(FilterArgument $arg): array|string|float|int|bool|null
    {
        if ($arg->getInputType() === null || $arg->getVarName() === null || $arg->getOptions() === null) {
            return null;
        }

        /**
         * @psalm-suppress PossiblyNullPropertyFetch
         * @psalm-suppress NoValue
         * @psalm-suppress InvalidArgument
         */
        return filter_input(type: $arg->getInputType()->value, var_name: $arg->getVarName(), filter: $arg->getFilter()->value, options: $arg->getOptions());
    }

    /**
     * Отфильтровать переменную при необходимости
     */
    public function varValue(array|string|float|int|bool|null $value, FilterArgument $arg): array|string|float|int|bool|null
    {
        if ($arg->getOptions() === null) {
            return $value;
        }

        /**
         * @psalm-suppress NoValue
         * @psalm-suppress InvalidArgument
         */
        return filter_var(value: $value, filter: $arg->getFilter()->value, options: $arg->getOptions());
    }

    /**
     * Получить массив внешних переменных и отфильтровать их при необходимости
     *
     * @param FilterArgument[] $args
     */
    public function inputVars(InputType $type, array $args, bool $addEmpty = true): array|false|null
    {
        if ($args === []) {
            return [];
        }

        if (count($args) === 1) {
            /** @var FilterArgument $arg */
            $arg = array_first($args);

            return [
                array_key_first($args) => $this->inputVarValue(arg: $arg),
            ];
        }

        $options = array_map(fn (FilterArgument $arg): array => $arg->getFlagOptions(), $args);

        return filter_input_array(type: $type->value, options: $options, add_empty: $addEmpty);
    }

    /**
     * Отфильтровать массив переменных при необходимости
     *
     * @param array<array-key, array|string|float|int|bool|null> $vars
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
            /** @var array|string|float|int|bool|null $var */
            $var = array_first($vars);

            /** @var FilterArgument $arg */
            $arg = array_first($args);

            return [
                array_key_first($args) => $this->varValue(value: $var, arg: $arg),
            ];
        }

        $options = array_map(fn (FilterArgument $arg): array => $arg->getFlagOptions(), $args);

        return filter_var_array(array: $vars, options: $options, add_empty: $addEmpty);
    }
}
