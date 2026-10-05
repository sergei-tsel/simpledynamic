<?php

declare(strict_types=1);

namespace Simpledynamic\Services\Filtration;

use BackedEnum;
use Closure;
use Simpledynamic\Services\Filtration\Sanitization\NumberFloatFilterFlag;
use Simpledynamic\Services\Filtration\Sanitization\SanitizationFilter;
use Simpledynamic\Services\Filtration\Sanitization\SanitizationFilterFlag;
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
use Simpledynamic\Services\Routing\InputType;

/**
 * Аргумент фильтра
 *
 * Описывает, каким фильтром и с какими флагами и опциями обрабатывается значение
 * внешней переменной. Флаги и опции, не поддерживаемые выбранным фильтром, отбрасываются,
 * чтобы filter_var() и filter_input() никогда не получали недопустимые аргументы
 */
readonly class FilterArgument
{
    /**
     * @param AlternativeFilter|SanitizationFilter|ValidationFilter $filter Фильтр значения
     * @param list<BackedEnum> $flags Флаги, совместимые с выбранным фильтром,
     *        собираются в битовую маску
     * @param array<string, mixed>|Closure $options Опции, совместимые с выбранным фильтром;
     *        для AlternativeFilter::CALLBACK здесь передаётся замыкание-фильтр
     * @param ?InputType $inputType Тип внешних переменных, из которых берётся значение;
     *        null, если аргумент не привязан к входным данным
     * @param ?string $varName Название внешней переменной; null, если аргумент не привязан к входным данным
     */
    public function __construct(
        private AlternativeFilter|SanitizationFilter|ValidationFilter $filter,
        private array $flags = [],
        private array|Closure $options = [],
        private ?InputType $inputType = null,
        private ?string $varName = null,
    ) {}

    /**
     * Получить фильтр значения
     */
    public function getFilter(): AlternativeFilter|SanitizationFilter|ValidationFilter
    {
        return $this->filter;
    }

    /**
     * Получить тип внешних переменных, из которых берётся значение
     */
    public function getInputType(): ?InputType
    {
        return $this->inputType;
    }

    /**
     * Получить название внешней переменной
     */
    public function getVarName(): ?string
    {
        return $this->varName;
    }

    /**
     * Получить опции фильтра
     *
     * Для AlternativeFilter::CALLBACK возвращается замыкание-фильтр, для остальных
     * фильтров — массив с совместимыми флагами и опциями. Флаги собираются в битовую
     * маску, потому что filter_var() и filter_input() принимают только целое число.
     * Пустые флаги и опции в массив не попадают: filter_var_array() отбрасывает
     * переменные, описанные пустыми флагами, а filter_var() для фильтров с обязательной
     * опцией, например FILTER_VALIDATE_REGEXP, выбрасывает ValueError на пустых опциях
     *
     * @return Closure|array{flags?: int, options?: array<string, mixed>}|null
     *         null, если применять к значению нечего
     */
    public function getOptions(): Closure|array|null
    {
        if ($this->filter === AlternativeFilter::CALLBACK) {
            return $this->options instanceof Closure ? $this->options : null;
        }

        return $this->validateFlagOptions();
    }

    /**
     * Получить определение фильтра для filter_input_array() и filter_var_array()
     *
     * Определение содержит обязательный фильтр и, если они есть, совместимые
     * с ним флаги и опции
     *
     * @return array{filter: int, flags?: int, options?: array<string, mixed>|Closure}
     */
    public function getFlagOptions(): array
    {
        $filter = ['filter' => $this->filter->value];
        $options = $this->getOptions();

        if ($options instanceof Closure) {
            return ['filter' => $filter['filter'], 'options' => $options];
        }

        return $options === null ? $filter : $filter + $options;
    }

    /**
     * Получить совместимые с фильтром флаги и опции
     *
     * Флаги, не поддерживаемые фильтром, отбрасываются, остальные собираются
     * в битовую маску: filter_var() и filter_input() принимают флаги только
     * как целое число, а массив флагов PHP игнорирует
     *
     * @return array{flags?: int, options?: array<string, mixed>}
     */
    protected function validateFlagOptions(): array
    {
        $map = $this->getFilterMap();

        $mask = 0;

        foreach ($this->flags as $flag) {
            $value = (int) $flag->value;

            if (in_array($value, $map['flags'], true)) {
                $mask |= $value;
            }
        }

        $flagOptions = $mask === 0 ? [] : ['flags' => $mask];

        if ($this->options instanceof Closure) {
            return $flagOptions;
        }

        $options = array_filter(
            $this->options,
            static fn(string|int $key): bool => in_array($key, $map['options'], true),
            ARRAY_FILTER_USE_KEY,
        );

        return $options === [] ? $flagOptions : $flagOptions + ['options' => $options];
    }

    /**
     * Получить карту флагов и опций, поддерживаемых выбранным фильтром
     *
     * @return array{flags: list<int>, options: list<string>}
     */
    protected function getFilterMap(): array
    {
        $map = [
            'flags' => array_map(static fn(BackedEnum $flag): int => (int) $flag->value, GenericFilterFlag::cases()),
            'options' => [],
        ];

        if ($this->filter instanceof SanitizationFilter) {
            $map['flags'] = array_merge($map['flags'], $this->getSanitizationMap()['flags']);

            return $map;
        }

        if ($this->filter instanceof ValidationFilter) {
            $validationMap = $this->getValidationMap();

            return [
                'flags' => array_merge($map['flags'], $validationMap['flags']),
                'options' => $validationMap['options'],
            ];
        }

        return $map;
    }

    /**
     * Получить карту флагов и опций, поддерживаемых фильтрами санитизации данных
     *
     * @return array{flags: list<int>, options: list<string>}
     */
    protected function getSanitizationMap(): array
    {
        $flags = $this->filter === SanitizationFilter::NUMBER_FLOAT
            ? array_merge(SanitizationFilterFlag::cases(), NumberFloatFilterFlag::cases())
            : SanitizationFilterFlag::cases();

        return [
            'flags' => array_map(static fn(BackedEnum $flag): int => (int) $flag->value, $flags),
            'options' => [],
        ];
    }

    /**
     * Получить карту флагов и опций, поддерживаемых фильтрами валидации данных
     *
     * @return array{flags: list<int>, options: list<string>}
     */
    protected function getValidationMap(): array
    {
        $filterMap = match ($this->filter) {
            ValidationFilter::INT => ['flags' => IntFilterFlag::cases(), 'options' => IntFilterOption::cases()],
            ValidationFilter::FLOAT => ['flags' => FloatFilterFlag::cases(), 'options' => FloatFilterOption::cases()],
            ValidationFilter::REGEXP => ['flags' => [], 'options' => RegexpFilterOption::cases()],
            ValidationFilter::URL => ['flags' => UrlFilterFlag::cases(), 'options' => []],
            ValidationFilter::DOMAIN => ['flags' => DomainFilterFlag::cases(), 'options' => []],
            ValidationFilter::EMAIL => ['flags' => EmailFilterFlag::cases(), 'options' => []],
            ValidationFilter::IP => ['flags' => IpFilterFlag::cases(), 'options' => []],
            default => ['flags' => [], 'options' => []],
        };

        $options = array_merge(ValidationFilterOption::cases(), $filterMap['options']);

        return [
            'flags' => array_map(static fn(BackedEnum $flag): int => (int) $flag->value, $filterMap['flags']),
            'options' => array_map(static fn(BackedEnum $option): string => (string) $option->value, $options),
        ];
    }
}
