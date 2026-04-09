<?php

declare(strict_types=1);

namespace Sympledynamic\Services\ParamsFiltration;

use Attribute;
use Sympledynamic\Services\Routing\InputType;

/**
 * Фильтруемый параметр
 */
#[Attribute(Attribute::TARGET_CLASS | Attribute::TARGET_METHOD | Attribute::IS_REPEATABLE)]
readonly class FilterParam extends FilterArgument
{
    public function __construct(
        private InputType $inputType,
        private string    $varName,
        private int       $filterId    = FILTER_UNSAFE_RAW,
        private array     $flags       = [],
        private array     $options     = [],
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
