<?php

declare(strict_types=1);

namespace Framework\Services\ParamsFiltration;

use Attribute;
use Framework\Services\Routing\InputTypes;

/**
 * Фильтруемый параметр
 */
#[Attribute(Attribute::TARGET_CLASS | Attribute::TARGET_METHOD | Attribute::IS_REPEATABLE)]
readonly class FilterParam extends FilterArgument
{
    public function __construct(
        private InputTypes $inputType,
        private string     $varName,
        private int        $filterId    = FILTER_UNSAFE_RAW,
        private array      $flags       = [],
        private array      $options     = [],
    ) {
        parent::__construct(
            filterId: $this->filterId,
            flags: $this->flags,
            options: $this->options,
            inputType: $this->inputType,
            varName: $this->varName,
        );
    }
}
