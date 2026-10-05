<?php

declare(strict_types=1);

namespace Simpledynamic\Integrations;

use Simpledynamic\Base\Model\BuilderInterface;

/**
 * Билдер для записи данных
 */
interface WriteBuilderInterface extends BuilderInterface
{
    /**
     * Создать
     *
     * @param array<string, scalar|null> $data Данные для сохранения
     */
    public function create(array $data): int;

    /**
     * Изменить
     *
     * @param array<string, scalar|null> $params Параметры поиска
     * @param array<string, scalar|null> $data Данные для сохранения
     */
    public function update(array $params, array $data): void;

    /**
     * Удалить
     *
     * @param array<string, scalar|null> $params Параметры поиска
     */
    public function delete(array $params): void;
}
