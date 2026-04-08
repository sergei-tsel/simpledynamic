<?php

declare(strict_types=1);

namespace Framework\Base\Model;

/**
 * Маппер
 */
class Mapper
{
    /**
     * Создать DTO из модели
     */
    public function modelToDto(Model $model): object
    {
        return new class () {};
    }
}
