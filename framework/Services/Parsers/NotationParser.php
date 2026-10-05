<?php

declare(strict_types=1);

namespace Simpledynamic\Services\Parsers;

use Simpledynamic\Base\View\ParserInterface;
use Simpledynamic\Services\Arrays\NotationBuilder;

/**
 * Парсер нотации
 */
final readonly class NotationParser implements ParserInterface
{
    public function __construct(
        /** @var non-empty-string */
        private string $separator = '.',
    ) {}

    /**
     * Перевести многомерный массив в нотацию
     *
     * @param array $data Данные
     * @return array
     */
    #[\Override]
    public function serialize(array $data): array
    {
        if ($data === []) {
            return [];
        }

        $result = [];
        $groups = [
            $data,
        ];

        for ($i = 0; count($groups[$i] ?? []) > 0; $i++) {
            $currentGroup = $groups[$i] ?? [];

            /** @var array|scalar|null $value */
            foreach ($currentGroup as $path => $value) {
                if (
                    !is_array($value)
                    || $value === []
                    || array_any($value, static fn($item): bool => !is_array($item))
                ) {
                    $result[$path] = $value;

                    continue;
                }

                /** @var array|scalar|null $item */
                foreach ($value as $key => $item) {
                    $groups[$i + 1][$path . $this->separator . $key] = $item;
                }
            }
        }

        return $result;
    }

    /**
     * Перевести нотацию в многомерный массив
     *
     * @param array $data Данные
     * @return array
     */
    #[\Override]
    public function deserialize(array $data): array
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
            new NotationBuilder(separator: $this->separator)->setValue(data: $result, path: $path, value: $value);
        }

        return $result;
    }
}
