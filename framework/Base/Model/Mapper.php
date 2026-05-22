<?php

declare(strict_types=1);

namespace Simpledynamic\Base\Model;

/**
 * Маппер
 *
 * @api
 * @psalm-suppress ClassCanBeFinal
 */
class Mapper
{
    /**
     * Создать DTO из модели
     *
     * @psalm-suppress PossiblyUnusedMethod
     * @psalm-suppress PossiblyUnusedParam
     */
    public function modelToDto(Model $model): object
    {
        return new class () {};
    }
}
