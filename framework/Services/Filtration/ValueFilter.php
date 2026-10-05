<?php

declare(strict_types=1);

namespace Simpledynamic\Services\Filtration;

use Closure;
use Simpledynamic\Services\Filtration\Sanitization\SanitizationFilter;

/**
 * Фильтр значений
 *
 * Применяет фильтр, описанный аргументом фильтра, к одиночным значениям
 * и к массивам значений
 */
final class ValueFilter
{
    /**
     * Отфильтровать значение при необходимости
     *
     * SanitizationFilter::UNSAFE_RAW означает отсутствие фильтрации, поэтому значение
     * возвращается как есть: filter_var() с фильтром санитизации возвращает false
     * для массивов, из-за чего теряются значения $_FILES и $_SESSION.
     * Значение без применимых флагов и опций тоже возвращается без изменений
     *
     * @param mixed $value Значение
     * @param FilterArgument $arg Аргумент фильтра
     * @return array<array-key, mixed>|string|float|int|bool|null
     */
    public function value(mixed $value, FilterArgument $arg): array|string|float|int|bool|null
    {
        if ($arg->getFilter() === SanitizationFilter::UNSAFE_RAW) {
            return $this->normalize($value);
        }

        $options = $arg->getOptions();

        if ($options instanceof Closure) {
            return $this->normalize($options($this->normalize($value)));
        }

        if ($options === null) {
            return $this->normalize($value);
        }

        return $this->normalize(filter_var(value: $value, filter: $arg->getFilter()->value, options: $options));
    }

    /**
     * Отфильтровать массив значений при необходимости
     *
     * Значения фильтруются по названиям переменных из аргументов фильтра: значения,
     * для которых аргумента нет, отбрасываются, как и названия переменных, значения
     * которых не переданы, если $addEmpty = false. Каждое значение фильтруется
     * отдельно, поэтому названия переменных не ограничены строками, как это требует
     * filter_var_array()
     *
     * @param array<array-key, mixed> $vars Значения по названиям переменных
     * @param array<string, FilterArgument> $args Аргументы фильтра по названиям переменных
     * @param bool $addEmpty Добавлять отсутствующие переменные с пустым значением
     * @return array<array-key, mixed> Отфильтрованные значения по названиям переменных
     */
    public function values(array $vars, array $args, bool $addEmpty = true): array
    {
        if ($args === []) {
            return $vars;
        }

        $filtered = [];

        foreach ($args as $name => $arg) {
            if (array_key_exists($name, $vars)) {
                $filtered[$name] = $this->value(value: $vars[$name], arg: $arg);

                continue;
            }

            if ($addEmpty) {
                $filtered[$name] = null;
            }
        }

        return $filtered;
    }

    /**
     * Привести значение к поддерживаемому типу значения
     *
     * filter_input() и filter_var() возвращают mixed, а значения из глобальных
     * массивов могут быть объектами, поэтому значение приводится к типу,
     * объявленном методами фильтра
     *
     * @param mixed $value Значение
     * @return array<array-key, mixed>|string|float|int|bool|null
     */
    public function normalize(mixed $value): array|string|float|int|bool|null
    {
        return is_array($value) || is_scalar($value) ? $value : null;
    }
}
