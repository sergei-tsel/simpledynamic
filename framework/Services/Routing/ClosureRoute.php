<?php

declare(strict_types=1);

namespace Simpledynamic\Services\Routing;

use Closure;
use Simpledynamic\Base\Controller\RouteInterface;
use Simpledynamic\Services\Configuration\Config;

/**
 * Роут с замыканием
 */
readonly class ClosureRoute implements RouteInterface
{
    public function __construct(
        private Closure $action,
        private string $method,
        private string $path,
        /** @var array<string, string> */
        private array $pathParams = [],
    ) {}

    /**
     * Получить экшен
     */
    #[\Override]
    public function getAction(): Closure
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
     * Вызвать замыкание
     *
     * @param array<string, mixed> $params Параметры
     */
    #[\Override]
    public function call(array $params, ?Config $config = null): mixed
    {
        return $this->getAction()(...$this->pathParams);
    }
}
