<?php

declare(strict_types=1);

namespace Simpledynamic\Services\Filtration;

use Simpledynamic\Services\Routing\InputType;

/**
 * Глобальные массивы внешних переменных
 *
 * Отвечает за чтение значений внешних переменных из глобальных массивов,
 * которые заполняются PHP при разборе запроса
 */
final class InputGlobals
{
    /**
     * Проверить, что внешняя переменная существует в исходных данных запроса
     *
     * @param InputType $type Тип внешних переменных
     * @param string $name Название внешней переменной
     */
    public function inputVarExists(InputType $type, string $name): bool
    {
        return filter_has_var($type->value, $name);
    }

    /**
     * Получить значение внешней переменной из глобального массива
     *
     * В отличие от filter_input() читает уже разобранное значение глобального
     * массива, поэтому работает и в CLI-режиме, где исходных данных запроса нет
     *
     * @param InputType $type Тип внешних переменных
     * @param string $name Название внешней переменной
     * @return array<array-key, mixed>|string|float|int|bool|null
     *         Значение переменной или null, если переменной нет
     */
    public function inputVarValue(InputType $type, string $name): array|string|float|int|bool|null
    {
        return match ($type) {
            InputType::POST => $_POST[$name] ?? null,
            InputType::GET => $_GET[$name] ?? null,
            InputType::COOKIE => $_COOKIE[$name] ?? null,
            InputType::ENV => $_ENV[$name] ?? null,
            InputType::SERVER => $_SERVER[$name] ?? null,
        };
    }
}
