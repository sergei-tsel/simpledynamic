<?php

declare(strict_types=1);

namespace Sympledynamic\Services\Filtration;

use Attribute;
use Closure;
use Simpledynamic\Services\Filtration\Sanitization\SanitizationFilter;
use Simpledynamic\Services\Filtration\Validation\ValidationFilter;
use Sympledynamic\Services\Routing\GlobalArray;
use Sympledynamic\Services\Routing\InputType;

/**
 * Фильтруемый параметр
 *
 * @psalm-suppress UnusedProperty
 */
#[Attribute(Attribute::TARGET_CLASS | Attribute::TARGET_METHOD | Attribute::IS_REPEATABLE)]
final readonly class FilterParam extends FilterArgument
{
    /**
     * @psalm-suppress PossiblyUnusedMethod
     */
    public function __construct(
        private InputType|GlobalArray                                 $type,
        private string                                                $varName,
        private ?Closure                                              $callback   = null,
        private AlternativeFilter|SanitizationFilter|ValidationFilter $filter     = SanitizationFilter::UNSAFE_RAW,
        private array                                                 $flags      = [],
        /** @var array<string, mixed> */
        private array                                                 $options    = [],
    ) {
        parent::__construct(
            filter: $this->filter,
            flags: $this->flags,
            options: $this->options,
            inputType: InputType::tryFrom($this->type->value),
            varName: $this->varName,
            callback: $this->callback,
        );
    }

    public function getType(): InputType|GlobalArray
    {
        return $this->type;
    }
}
