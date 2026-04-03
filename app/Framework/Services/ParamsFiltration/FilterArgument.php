<?php

declare(strict_types=1);

namespace App\Framework\Services\ParamsFiltration;

use App\Framework\Services\Routing\InputTypes;

/**
 * Аргумент фильтра
 */
readonly class FilterArgument
{
    public function __construct(
        private int         $filterId,
        private ?array      $flags     = [],
        private ?array      $options   = [],
        private ?InputTypes $inputType = null,
        private ?string     $varName   = null,
    ) {
    }

    public function getFilterId(): int
    {
        return $this->filterId;
    }

    public function getInputType(): ?InputTypes
    {
        return $this->inputType;
    }

    public function getVarName(): ?string
    {
        return $this->varName;
    }

    /**
     * Получить ассоциативный массив с флагами и опциями
     *
     * @return (array|null)[]
     *
     * @psalm-return array{flags?: array|null, options?: array|null}
     */
    public function getFlagOptions(): array
    {
        $flagOptions = [];

        if ($this->flags !== []) {
            $flagOptions['flags'] = $this->flags;
        }

        if ($this->options !== []) {
            $flagOptions['options'] = $this->options;
        }

        return $flagOptions;
    }

    /**
     * Получить ассоциативный массив с фильтром, флагами и опциями
     *
     * @return array{filter: int, flags?: array, options?: array}
     */
    public function getFilterFlagOptions(): array
    {
        $params = $this->getFlagOptions();

        $params['filter'] = $this->getFilterId();

        return $params;
    }
}
