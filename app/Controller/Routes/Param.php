<?php
declare(strict_types=1);
namespace App\Controller\Routes;

use Attribute;

#[Attribute(Attribute::TARGET_CLASS|Attribute::TARGET_METHOD|Attribute::TARGET_PROPERTY|Attribute::TARGET_CLASS_CONSTANT|Attribute::IS_REPEATABLE)]
/**
 * Фильтруемый параметр
 */
readonly class Param
{
    public function __construct(
        private ParamTypes $type,
        private string     $name,
        private int        $filter  = FILTER_DEFAULT,
        private int        $flags   = 0,
        private array      $options = [],
    )
    {
    }

    /**
     * Получить аргумент для фильтрования
     */
    public function getArgument(): array
    {
        $argument = [
            'type' => $this->type->value,
        ];

        if (!($this->flags || $this->options)) {
           $argument['param'] = [
               $this->name => $this->filter,
           ];

           return $argument;
        }

        $argument['param'] = [
            $this->name => [
                'filter'  => $this->filter,
            ],
        ];

        if ($this->flags) {
            $argument['param'][$this->name]['flags'] = $this->flags;
        }

        if ($this->options) {
            $argument['param'][$this->name]['options'] = $this->options;
        }

        return $argument;
    }
}