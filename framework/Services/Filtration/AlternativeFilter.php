<?php

declare(strict_types=1);

namespace Simpledynamic\Services\Filtration;

/**
 * Альтернативный фильтр
 *
 * Фильтры, отсутствующие среди фильтров санитизации и валидации данных
 */
enum AlternativeFilter: int
{
    private const int FILTER_CALLBACK = FILTER_CALLBACK;

    /**
     * Замыкание-фильтр: значение обрабатывается переданным в опциях замыканием
     */
    case CALLBACK = self::FILTER_CALLBACK;
}
