<?php

declare(strict_types=1);

namespace Sympledynamic\Services\Filtration;

use Closure;
use Simpledynamic\Services\Filtration\Sanitization\NumberFloatFilterFlag;
use Simpledynamic\Services\Filtration\Sanitization\SanitizationFilter;
use Simpledynamic\Services\Filtration\Validation\DomainFilterFlag;
use Simpledynamic\Services\Filtration\Validation\EmailFilterFlag;
use Simpledynamic\Services\Filtration\Validation\FloatFilterFlag;
use Simpledynamic\Services\Filtration\Validation\FloatFilterOption;
use Simpledynamic\Services\Filtration\Validation\IntFilterFlag;
use Simpledynamic\Services\Filtration\Validation\IntFilterOption;
use Simpledynamic\Services\Filtration\Validation\IpFilterFlag;
use Simpledynamic\Services\Filtration\Validation\RegexpFilterOption;
use Simpledynamic\Services\Filtration\Validation\UrlFilterFlag;
use Simpledynamic\Services\Filtration\Validation\ValidationFilter;
use Simpledynamic\Services\Filtration\Validation\ValidationFilterOption;
use Sympledynamic\Services\Filtration\Sanitization\SanitizationFilterFlag;
use Sympledynamic\Services\Routing\InputType;

/**
 * Аргумент фильтра
 */
readonly class FilterArgument
{
    public function __construct(
        private AlternativeFilter|SanitizationFilter|ValidationFilter $filter,
        private array                                                 $flags     = [],
        private array                                                 $options   = [],
        private ?InputType                                            $inputType = null,
        private ?string                                               $varName   = null,
        private ?Closure                                              $callback  = null,
    ) {
    }

    public function getFilter(): AlternativeFilter|SanitizationFilter|ValidationFilter
    {
        return $this->filter;
    }

    public function getInputType(): ?InputType
    {
        return $this->inputType;
    }

    public function getVarName(): ?string
    {
        return $this->varName;
    }

    /**
     * Получить опции фильтра
     *
     * @return Closure|array|null
     */
    public function getOptions(): Closure|array|null
    {
        return $this->filter === AlternativeFilter::CALLBACK
            ? $this->callback
            : $this->validateFlagOptions();
    }

    /**
     * Получить ассоциативный массив с фильтром, флагами и опциями
     *
     * @return array{filter: int, flags?: array<int, int>, options?: Closure|array<string, int|float|string>|null}
     */
    public function getFlagOptions(): array
    {
        return $this->filter === AlternativeFilter::CALLBACK
            ? [
                'filter'  => $this->filter->value,
                'options' => $this->callback,
              ]
            : array_merge([
                'filter'  => $this->filter->value,
              ], $this->validateFlagOptions());
    }

    /**
     * Получить ассоциативный массив с флагами и опциями
     *
     * @return array{flags?: array, options?: array<string, int|float|string>}
     */
    protected function validateFlagOptions(): array
    {
        if ($this->flags === [] && $this->options === []) {
            return [];
        }

        $map = $this->getFilterMap();

        $flagOptions = [];

        if ($this->flags !== []) {
            $flagOptions['flags'] = array_filter(array_map(fn (\BackedEnum $flag): int => (int) $flag->value, $this->flags), fn (int $flag): bool => in_array($flag, $map['flags']));
        }

        if ($this->options !== []) {
            $flagOptions['options'] = array_filter($this->options, fn (string $key): bool => in_array($key, $map['options']), ARRAY_FILTER_USE_KEY);
        }

        return $flagOptions;
    }

    /**
     * Получить карту поддерживаемых флагов и опций фильтра
     *
     * @return array{flags: list<int>, options?: list<string>}
     */
    protected function getFilterMap(): array
    {
        $map = [
            'flags' => GenericFilterFlag::cases(),
        ];

        if (in_array($this->filter, SanitizationFilter::cases())) {
            $map = array_merge_recursive($map, $this->getSanitizationMap());
        } elseif (in_array($this->filter, ValidationFilter::cases())) {
            $map = array_merge_recursive($map, $this->getValidationMap());
        }

        return $map;
    }

    /**
     * Получить карту поддерживаемых флагов и опций для санитизации данных
     *
     * @return array{flags: list<int>}
     */
    protected function getSanitizationMap(): array
    {
        $flags = SanitizationFilterFlag::cases();

        if ($this->filter === SanitizationFilter::NUMBER_FLOAT) {
            $flags = array_merge($flags, NumberFloatFilterFlag::cases());
        }

        return [
            'flags' => array_map(fn (SanitizationFilterFlag|SanitizationFilter|NumberFloatFilterFlag $flag): int => $flag->value, $flags),
        ];
    }

    /**
     * Получить карту поддерживаемых флагов и опций для валидации данных
     *
     * @return array{flags?: list<int>, options?: list<string>}
     */
    protected function getValidationMap(): array
    {
        $options = ValidationFilterOption::cases();

        $flagOptions = match ($this->filter) {
            ValidationFilter::INT    => [
                'flags'  => IntFilterFlag::cases(),
                'options' => IntFilterOption::cases(),
            ],
            ValidationFilter::FLOAT  => [
                'flags'   => FloatFilterFlag::cases(),
                'options' => FloatFilterOption::cases(),
            ],
            ValidationFilter::REGEXP => [
                'options' => RegexpFilterOption::cases(),
            ],
            ValidationFilter::URL    => [
                'flags' => UrlFilterFlag::cases(),
            ],
            ValidationFilter::DOMAIN => [
                'flags' => DomainFilterFlag::cases(),
            ],
            ValidationFilter::EMAIL  => [
                'flags' => EmailFilterFlag::cases(),
            ],
            ValidationFilter::IP     => [
                'flags' => IpFilterFlag::cases(),
            ],
            default                  => [],
        };

        if (array_key_exists('options', $flagOptions)) {
            $options = array_merge($options, $flagOptions['options']);
        }

        return [
            'flags'   => array_map(fn (IntFilterFlag|FloatFilterFlag|UrlFilterFlag|DomainFilterFlag|EmailFilterFlag|IpFilterFlag $flag): int => $flag->value, $flagOptions['flags']),
            'options' => array_map(fn (ValidationFilterOption|IntFilterOption|FloatFilterOption|RegexpFilterOption $option): string => $option->value, $options),
        ];
    }
}
