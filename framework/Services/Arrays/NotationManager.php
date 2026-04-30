<?php

declare(strict_types=1);

namespace Sympledynamic\Services\Arrays;

/**
 * Сервис для управления нотацией
 */
final readonly class NotationManager
{
    public function __construct(
        /** @var non-empty-string */
        private string $separator = '.',
    ) {
    }

    /**
     * Создать сервис для управления нотацией с переданным сепаратором
     *
     * @param non-empty-string $separator
     */
    public static function instanceOne(string $separator = '.'): NotationManager
    {
        return new self($separator);
    }

    /**
     * Получить элемент вложенного массива по нотации
     *
     * @psalm-suppress PossiblyUnusedMethod
     */
    public function getValue(array $data, string $path): mixed
    {
        $keys = explode($this->separator, $path);
        $value = $data;

        foreach ($keys as $key) {
            if (!is_array($value) || !array_key_exists($key, $value)) {
                return null;
            }

            /** @var array|scalar|null $value */
            $value = $value[$key];
        }

        return $value;
    }

    /**
     * Положить элемент во вложенный массив по нотации
     *
     * @param object|array|scalar|null $value
     */
    public function setValue(array &$data, string $path, mixed $value): void
    {
        if ($data === []) {
            return;
        }

        $keys = explode($this->separator, $path);
        $level = &$data;

        foreach ($keys as $index => $key) {
            if ($index === count($keys) - 1) {
                $level[$key] = $value;
                return;
            }

            if (!isset($level[$key]) || !is_array($level[$key])) {
                $level[$key] = [];
            }

            $level = &$level[$key];
        }
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
            /** @var array|scalar|null $value */
            foreach ($groups[$i] as $path => $value) {
                if (!is_array($value) || $value === [] || array_any($value, fn ($item): bool => !is_array($item))) {
                    $result[$path] = $value;

                    continue;
                }

                /** @var array|scalar|null $item */
                foreach ($value as $key => $item) {
                    $groups[++$i][$path . $this->separator . $key] = $item;
                }
            }
        }

        return $result;
    }

    /**
     * Перевести нотацию в многомерный массив
     *
     * @psalm-suppress PossiblyUnusedMethod
     */
    public function toMdsArray(array $data): array
    {
        if ($data === []) {
            return [];
        }

        $result = [];

        /**
         * @var string $path
         * @var string $value
         */
        foreach ($data as $path => $value) {
            $this->setValue(data: $result, path: $path, value: $value);
        }

        return $result;
    }
}
