<?php

declare(strict_types=1);

namespace App\Framework\Services\Arrays;

/**
 * Сервис для управления нотацией
 */
readonly class NotationManager
{
    public function __construct(
        private string $separator,
    ) {
    }

    /**
     * Создать сервис для управления нотацией с переданным сепаратором
     */
    public static function instanceOne(string $separator): NotationManager
    {
        return new self($separator);
    }

    /**
     * Получить элемент вложенного массива по нотации
     */
    public function getValue(array $data, string $path): mixed
    {
        $keys = explode($this->separator, $path);
        $value = $data;

        foreach ($keys as $key) {
            if (!is_array($value) || !array_key_exists($key, $value)) {
                return null;
            }

            $value = $value[$key];
        }

        return $value;
    }

    /**
     * Положить элемент во вложенный массив по нотации
     */
    public function setValue(array &$data, string $path, mixed $value): void
    {
        if ($data === []) {
            return;
        }

        $keys = explode($this->separator, $path);
        $level = &$data;

        foreach ($keys as $index => $key) {
            if ($index !== count($keys) - 1) {
                if (!isset($level[$key]) || !is_array($level[$key])) {
                    $level[$key] = [];
                }

                $level = &$level[$key];
            }
        }

        $level[end($keys)] = $value;
    }

    /**
     * Перевести многомерный массив в нотацию
     */
    public function fromMdsArray(array $data): array
    {
        if ($data === []) {
            return [];
        }

        $result = [];
        $groups = [
            $data,
        ];

        for ($i = 0; count($groups[$i] ?? []) > 0; $i++) {
            foreach ($groups[$i] as $path => $value) {
                if (!is_array($value) || $value === [] || array_any($value, fn ($item): bool => !is_array($item))) {
                    $result[$path] = $value;

                    continue;
                }

                foreach ($value as $key => $item) {
                    $groups[++$i][$path . $this->separator . $key] = $item;
                }
            }
        }

        return $result;
    }

    /**
     * Перевести нотацию в многомерный массив
     */
    public function toMdsArray(array $data): array
    {
        if ($data === []) {
            return [];
        }

        $result = [];

        foreach ($data as $path => $value) {
            $this->setValue($result, $path, $value);
        }

        return $result;
    }
}
