<?php

declare(strict_types=1);

namespace Simpledynamic\Services\Filtration;

use Attribute;
use BackedEnum;
use Closure;
use Simpledynamic\Services\Filtration\Sanitization\SanitizationFilter;
use Simpledynamic\Services\Filtration\Validation\ValidationFilter;
use Simpledynamic\Services\Routing\GlobalArray;
use Simpledynamic\Services\Routing\InputType;

/**
 * Фильтруемый параметр
 *
 * Атрибут, описывающий внешнюю переменную, значение которой нужно получить и отфильтровать
 */
#[Attribute(Attribute::TARGET_CLASS | Attribute::TARGET_METHOD | Attribute::IS_REPEATABLE)]
final readonly class FilterParam extends FilterArgument
{
    /**
     * @param InputType|GlobalArray $type Тип внешних переменных или глобальный массив,
     *        из которого берётся значение; глобальные массивы не имеют InputType,
     *        поэтому значение для них всегда читается напрямую
     * @param string $varName Название внешней переменной
     * @param AlternativeFilter|SanitizationFilter|ValidationFilter $filter Фильтр значения
     * @param list<BackedEnum> $flags Флаги, совместимые с выбранным фильтром,
     *        собираются в битовую маску
     * @param array<string, mixed>|Closure $options Опции, совместимые с выбранным фильтром;
     *        для AlternativeFilter::CALLBACK здесь передаётся замыкание-фильтр
     */
    public function __construct(
        private InputType|GlobalArray $type,
        string $varName,
        AlternativeFilter|SanitizationFilter|ValidationFilter $filter = SanitizationFilter::UNSAFE_RAW,
        array $flags = [],
        array|Closure $options = [],
    ) {
        parent::__construct(
            filter: $filter,
            flags: $flags,
            options: $options,
            inputType: $type instanceof InputType ? $type : null,
            varName: $varName,
        );
    }

    /**
     * Получить источник значения параметра
     *
     * @return InputType|GlobalArray Тип внешних переменных или глобальный массив
     */
    public function getType(): InputType|GlobalArray
    {
        return $this->type;
    }
}
