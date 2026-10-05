<?php

declare(strict_types=1);

namespace Simpledynamic\Services\Filtration;

use Closure;
use Simpledynamic\Services\Routing\InputType;

/**
 * Фильтр внешних переменных
 *
 * Получает значения внешних переменных и передаёт их фильтру значений: переменные,
 * присутствующие в исходных данных запроса, фильтруются одним вызовом
 * filter_input_array(), остальные читаются из глобальных массивов
 */
final class InputFilter
{
    public function __construct(
        private readonly InputGlobals $inputGlobals = new InputGlobals(),
        private readonly ValueFilter $valueFilter = new ValueFilter(),
    ) {}

    /**
     * Получить массив внешних переменных и отфильтровать их при необходимости
     *
     * Все аргументы читаются из типа внешних переменных $type: аргументы без названия
     * переменной отбрасываются, а аргументы, отсутствующие и в исходных данных запроса,
     * и в глобальном массиве, попадают в результат только при $addEmpty = true
     *
     * @param InputType $type Тип внешних переменных
     * @param array<string, FilterArgument> $args Аргументы фильтра по названиям переменных
     * @param bool $addEmpty Добавлять отсутствующие переменные с пустым значением
     * @return array<array-key, mixed>|false|null Значения по названиям переменных
     */
    public function inputVars(InputType $type, array $args, bool $addEmpty = true): array|false|null
    {
        if ($args === []) {
            return [];
        }

        $source = $this->splitArgsBySource(type: $type, args: $args);

        $globalVars = $this->values(
            vars: $source['values'],
            args: $addEmpty ? $source['globalArgs'] + $source['missingArgs'] : $source['globalArgs'],
            addEmpty: $addEmpty,
        );

        if ($source['input'] === []) {
            return $globalVars;
        }

        $inputVars = filter_input_array(
            type: $type->value,
            options: $this->getDefinitions(args: $source['input']),
            add_empty: $addEmpty,
        );

        return $this->mergeVars(inputVars: $inputVars, globalVars: $globalVars);
    }

    /**
     * Отфильтровать массив значений при необходимости
     *
     * @param array<array-key, mixed> $vars Значения по названиям переменных
     * @param array<string, FilterArgument> $args Аргументы фильтра по названиям переменных
     * @param bool $addEmpty Добавлять отсутствующие переменные с пустым значением
     * @return array<array-key, mixed> Отфильтрованные значения по названиям переменных
     */
    public function values(array $vars, array $args, bool $addEmpty = true): array
    {
        return $this->valueFilter->values(vars: $vars, args: $args, addEmpty: $addEmpty);
    }

    /**
     * Разделить аргументы фильтра по источнику значения
     *
     * Аргументы переменных, отсутствующих и в исходных данных запроса, и в глобальном
     * массиве, попадают в отсутствующие аргументы, а не отбрасываются: они нужны
     * варианту с пустым значением. filter_input_array() принимает только названия
     * строк, поэтому переменные с числовыми названиями всегда читаются из
     * глобального массива
     *
     * @param InputType $type Тип внешних переменных
     * @param array<string, FilterArgument> $args Аргументы фильтра по названиям переменных
     * @return array{
     *     input: array<string, FilterArgument>,
     *     values: array<array-key, mixed>,
     *     globalArgs: array<string, FilterArgument>,
     *     missingArgs: array<string, FilterArgument>
     * }
     */
    private function splitArgsBySource(InputType $type, array $args): array
    {
        $input = [];
        $values = [];
        $globalArgs = [];
        $missingArgs = [];

        foreach ($args as $arg) {
            $name = $arg->getVarName();

            if ($name === null) {
                continue;
            }

            if (!self::isNumericName($name) && $this->inputGlobals->inputVarExists(type: $type, name: $name)) {
                $input[$name] = $arg;

                continue;
            }

            $value = $this->inputGlobals->inputVarValue(type: $type, name: $name);

            if ($value === null) {
                $missingArgs[$name] = $arg;

                continue;
            }

            $values[$name] = $value;
            $globalArgs[$name] = $arg;
        }

        return [
            'input' => $input,
            'values' => $values,
            'globalArgs' => $globalArgs,
            'missingArgs' => $missingArgs,
        ];
    }

    /**
     * Получить определения фильтров по названиям переменных
     *
     * @param array<string, FilterArgument> $args Аргументы фильтра по названиям переменных
     * @return array<string, array{filter: int, flags?: int, options?: array<string, mixed>|Closure}>
     */
    private function getDefinitions(array $args): array
    {
        return array_map(static fn(FilterArgument $arg): array => $arg->getFlagOptions(), $args);
    }

    /**
     * Объединить значения из исходных данных запроса и из глобальных массивов
     *
     * Значения из глобальных массивов имеют приоритет: исходные данные запроса
     * не содержат переменных, прочитанных из глобальных массивов. Используется
     * array_replace(), поскольку array_merge() перенумеровывает числовые названия переменных
     *
     * @param array<array-key, mixed>|false|null $inputVars Значения из исходных данных запроса
     * @param array<array-key, mixed> $globalVars Значения из глобальных массивов
     * @return array<array-key, mixed>|false|null
     */
    private function mergeVars(array|false|null $inputVars, array $globalVars): array|false|null
    {
        if ($globalVars === []) {
            return $inputVars;
        }

        return is_array($inputVars) ? array_replace($inputVars, $globalVars) : $globalVars;
    }

    /**
     * Проверить, что название переменной интерпретируется как числовой ключ массива
     *
     * PHP превращает название в целочисленный ключ массива, поэтому такие названия
     * нельзя передавать в filter_input_array()
     *
     * @param string $name Название переменной
     */
    private static function isNumericName(string $name): bool
    {
        return (string) (int) $name === $name;
    }
}
