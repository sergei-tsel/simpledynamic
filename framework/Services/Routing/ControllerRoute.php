<?php

declare(strict_types=1);

namespace Simpledynamic\Services\Routing;

use Simpledynamic\Base\Controller\RouteInterface;
use Simpledynamic\Container\ServiceContainer;
use Simpledynamic\Services\Configuration\Config;
use Simpledynamic\Services\Reflection\MethodReflectionBuilder;

/**
 * Роут с методом контроллера
 */
readonly class ControllerRoute implements RouteInterface
{
    public function __construct(
        /** @var class-string */
        private string $controllerName,
        private string $action,
        private string $method,
        private string $path,
        /** @var array<string, string> */
        private array $pathParams = [],
    ) {}

    /**
     * Получить экшен
     */
    #[\Override]
    public function getAction(): string
    {
        return $this->action;
    }

    /**
     * Получить HTTP-метод
     */
    #[\Override]
    public function getMethod(): string
    {
        return $this->method;
    }

    /**
     * Получить путь
     */
    #[\Override]
    public function getPath(): string
    {
        return $this->path;
    }

    /**
     * Получить имя
     */
    #[\Override]
    public function getName(): string
    {
        return $this->path;
    }

    /**
     * Получить имя контроллера
     *
     * @return class-string
     */
    public function getControllerName(): string
    {
        return $this->controllerName;
    }

    /**
     * Вызвать мидлвары контроллера и его метода и сам метод контроллера
     *
     * Внешние параметры передаются в параметр request, а значения PATH-параметров —
     * в одноимённые параметры метода, объявленные после request.
     *
     * @param array<string, mixed> $params Список отфильтрованных внешних параметров
     * @throws \Exception
     * @throws \ReflectionException
     */
    #[\Override]
    public function call(array $params, ?Config $config = null): mixed
    {
        $container = ServiceContainer::getInstance($config);

        return $container->execute(
            className: $this->controllerName,
            methodName: $this->getAction(),
            params: $this->collectParams($params),
        );
    }

    /**
     * Собрать параметры метода контроллера по именам его аргументов
     *
     * В request попадают только внешние параметры: значения PATH-параметров роута
     * передаются отдельными аргументами и не смешиваются с ними.
     *
     * @param array<string, mixed> $params Внешние параметры
     * @return array<string, mixed> Значения, подходящие аргументам метода
     * @throws \ReflectionException
     */
    private function collectParams(array $params): array
    {
        $methodBuilder = new MethodReflectionBuilder(
            reflectionMethod: new \ReflectionMethod($this->controllerName, $this->getAction()),
        );

        $methodParams = [];

        foreach ($methodBuilder->getParamsNames() as $paramsName) {
            if ($paramsName === 'request') {
                $methodParams[$paramsName] = $params;

                continue;
            }

            if (array_key_exists($paramsName, $this->pathParams)) {
                $methodParams[$paramsName] = $this->pathParams[$paramsName];
            }
        }

        return $methodParams;
    }
}
