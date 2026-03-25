<?php

declare(strict_types=1);

namespace App\Framework\Services\Routing;

use Attribute;
use config\Env;

/**
 * Фильтруемый параметр
 */
#[Attribute(Attribute::TARGET_CLASS | Attribute::TARGET_METHOD | Attribute::TARGET_PROPERTY | Attribute::TARGET_CLASS_CONSTANT | Attribute::IS_REPEATABLE)]
readonly class Param
{
    public function __construct(
        private ParamTypes $type,
        private string     $name,
        private string     $config  = Env::class,
        private int        $filter  = FILTER_UNSAFE_RAW,
        private int        $flags   = 0,
        private array      $options = [],
    ) {
    }

    /**
     * Получить аргумент для фильтрования
     *
     * @return array{
     *     type: int,
     *     config?: string,
     *     param: array<string, array{
     *          filter: int,
     *          flags?: int,
     *          options?: array
     *             }|int>
     * }
     */
    public function getArgument(): array
    {
        $argument = [
            'type' => $this->type->getEquivalent(),
        ];

        if ($this->type === ParamTypes::CONFIG) {
            $argument[$this->type->value] = $this->config;
        }

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
