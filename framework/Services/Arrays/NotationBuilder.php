<?php

declare(strict_types=1);

namespace Simpledynamic\Services\Arrays;

use Simpledynamic\Base\Model\BuilderInterface;

/**
 * Билдер нотации
 */
final readonly class NotationBuilder implements BuilderInterface
{
    public function __construct(
        /** @var non-empty-string */
        private string $separator = '.',
    ) {}

    /**
     * Получить элемент вложенного массива по нотации
     *
     * @param array $data Данные
     * @param string $path Путь к данным
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
     * @param array $data Данные
     * @param object|array|scalar|null $value Элемент
     */
    public function setValue(array &$data, string $path, mixed $value): void
    {
        if ($data === []) {
            return;
        }

        $keys = explode($this->separator, $path);
        $level = &$data;

        foreach ($keys as $index => $key) {
            if ($index === (count($keys) - 1)) {
                $level[$key] = $value;
                return;
            }

            if (!array_key_exists($key, $level) || !is_array($level[$key])) {
                $level[$key] = [];
            }

            $level = &$level[$key];
        }
    }
}
