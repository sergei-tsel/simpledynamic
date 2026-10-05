<?php

declare(strict_types=1);

namespace Simpledynamic\Services\Routing;

use Simpledynamic\Base\Model\BuilderInterface;
use Simpledynamic\Services\Configuration\Routes;
use Simpledynamic\Services\Filtration\Filter;
use Simpledynamic\Services\Filtration\FilterArgument;
use Simpledynamic\Services\Filtration\FilterParam;
use Simpledynamic\Services\Filtration\Sanitization\SanitizationFilter;
use Simpledynamic\Services\Filtration\Validation\ValidationFilter;
use Uri\Rfc3986\Uri;

/**
 * Строитель URI
 */
readonly class UriBuilder implements BuilderInterface
{
    public function __construct(
        private Filter $filter = new Filter(),
    ) {}

    /**
     * Создать URI с переданным путём
     */
    public function build(string $path): Uri
    {
        /**
         * @var string $base
         */
        $base = Routes::getConfigPart('base');

        $url = $this->filter->varValue(value: $base . $path, arg: new FilterArgument(filter: ValidationFilter::URL));

        return new Uri(uri: is_string($url) ? $url : '/', baseUrl: $this->getUri(uri: $base));
    }

    /**
     * Создать URI с REQUEST_URI
     */
    public function buildRequestUri(): Uri
    {
        $requestUri = $this->filter->inputVarValue(arg: new FilterParam(
            type: InputType::SERVER,
            varName: 'REQUEST_URI',
            filter: SanitizationFilter::URL,
        ));

        return $this->build(path: is_string($requestUri) ? $requestUri : '/');
    }

    /**
     * Создать URI
     */
    public function getUri(string $uri): Uri
    {
        return new Uri(uri: $uri);
    }

    /**
     * Получить PATH-параметры по переданному пути роута
     *
     * @return array<string, string> Список PATH-параметров
     */
    public function getPathParams(string $routePath, string $uriPath): array
    {
        $matches = [];

        preg_match('#' . $this->getParamsPattern(path: $routePath) . '#u', $uriPath, $matches);

        $pathParams = [];

        foreach ($matches as $name => $value) {
            if (!is_string($name)) {
                continue;
            }

            // Значение PATH-параметра приходит URL-Encoded и возвращается декодированным
            $pathParams[$name] = rawurldecode($value);
        }

        return $pathParams;
    }

    /**
     * Найти шаблон пути роута, соответствующий переданному пути
     *
     * @param array<string, mixed> $routes Роуты группы
     * @return string|null Найденный шаблон пути
     */
    public function findPattern(array $routes, string $uriPath): ?string
    {
        foreach (array_keys($routes) as $pattern) {
            if ($this->matchByName(routePath: $pattern, uriPath: $uriPath)) {
                return $pattern;
            }
        }

        return null;
    }

    /**
     * Проверить соответствие первого переданного пути второму
     */
    public function matchByName(string $routePath, string $uriPath): bool
    {
        return preg_match($this->getMatchPattern(path: $routePath), $uriPath) === 1;
    }

    /**
     * Получить паттерн PATH-параметров пути роута
     *
     * Значение PATH-параметра — любой непустой фрагмент пути, поэтому оно может
     * содержать не только цифры.
     */
    private function getParamsPattern(string $path): string
    {
        $pattern = preg_replace('#\{(\w+)}#u', '(?P<$1>[^/]+)', $path);

        return (is_string($pattern) ? $pattern : $path) . '$';
    }

    /**
     * Получить паттерн пути роута
     */
    private function getMatchPattern(string $path): string
    {
        $pattern = preg_replace('#\{\w+}#u', '[^/]+', $path);

        return '#^' . (is_string($pattern) ? $pattern : $path) . '$#u';
    }
}
