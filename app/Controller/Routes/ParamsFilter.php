<?php
declare(strict_types=1);
namespace App\Controller\Routes;

use ReflectionAttribute;
use ReflectionClass;

/**
 * Фильтр параметров
 */
class ParamsFilter
{
    public function __construct(
        private array $options = [],
    )
    {
    }

    /**
     * Прочитать атрибуты
     */
    public function readAttributes(object|string $class, ?string $name = null): ParamsFilter
    {
        $reflectionClass = new ReflectionClass($class);

        $reflection = match (true) {
            $name === null                       => $reflectionClass,
            $reflectionClass->hasMethod($name)   => $reflectionClass->getMethod($name),
            $reflectionClass->hasProperty($name) => $reflectionClass->getProperty($name),
            $reflectionClass->hasConstant($name) => $reflectionClass->getReflectionConstant($name),
        };

        $attributes = $reflection->getAttributes(Param::class);

        foreach ($attributes as $attribute) {
            /**
             * @var ReflectionAttribute $attribute
             * @var Param               $instance
             */
            $instance = $attribute->newInstance();

            $argument = $instance->getArgument();

            $this->options[$argument['type']] = $argument['param'];
        }

        return $this;
    }

    /**
     * Получить отфильтрованные данные
     */
    public function getFilteredData(array $options = []): array
    {
        if ($options) {
            foreach ($options as $type => $rules) {
                if (array_key_exists($type, $this->options)) {
                    $this->options[$type] = array_merge($this->options[$type], $rules);
                } else {
                    $this->options[$type] = $rules;
                }
            }
        }

        $params = [];

        foreach ($this->options as $type => $rules) {
            $params[$type] = match ($type) {
                INPUT_POST,
                INPUT_GET,
                INPUT_COOKIE,
                INPUT_ENV,
                INPUT_SERVER                         => filter_input_array($type, $rules, false),
                ParamTypes::FILES->getEquivalent()   => filter_var_array($_FILES, $rules, false),
                ParamTypes::SESSION->getEquivalent() => filter_var_array($_SESSION, $rules, false),
            };
        }

        return $params;
    }
}