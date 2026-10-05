<?php

declare(strict_types=1);

namespace Simpledynamic\Base\View;

/**
 * Представление
 */
interface ViewInterface
{
    /**
     * Проверить существование шаблона
     */
    public function exists(): ?ViewInterface;

    /**
     * Загрузить и интерполировать шаблон
     *
     * @param array $data Данные для подстановки в переменные
     */
    public function render(array $data = []): string;
}
