<?php

declare(strict_types=1);

namespace Simpledynamic\Services\Routing;

use Simpledynamic\Base\Controller\RouteInterface;
use Simpledynamic\Services\Configuration\Config;
use Simpledynamic\Services\Configuration\Routes;
use Simpledynamic\Services\Logging\Logger;

/**
 * Роутер
 *
 * Поиск роута выполняется по схеме:
 *  1. из пути исключается префикс, назначенный в конфиге ключу base;
 *  2. по первому фрагменту оставшегося пути определяется группа роутов;
 *  3. из пути исключается ключ группы, поэтому ключи роутов группы хранят путь
 *     без этого ключа;
 *  4. по оставшейся части пути в массиве группы ищется роут, ключ которого —
 *     шаблон пути, а значение содержит название HTTP-метода и экшен.
 *
 * Если группа не определена, но вызван базовый путь, выполняется перенаправление
 * на путь группы роутов, вызываемой по умолчанию.
 */
final class Router
{
    public function __construct(
        private readonly UriBuilder $uriBuilder = new UriBuilder(),
        private readonly RouteBuilder $routeBuilder = new RouteBuilder(),
        private readonly InputResolver $inputResolver = new InputResolver(),
        private readonly MiddlewaresResolver $middlewaresResolver = new MiddlewaresResolver(
            inputResolver: new InputResolver(),
        ),
    ) {}

    private ?Config $config = null;

    /**
     * Установить конфигурацию приложения
     */
    public function setConfig(?Config $config): void
    {
        $this->config = $config;
    }

    /**
     * Обработать запрос к сборке приложения
     *
     * @throws \Exception
     * @throws \ReflectionException
     */
    public function handle(): mixed
    {
        $path = $this->stripBase(path: $this->uriBuilder->buildRequestUri()->getPath());
        $groupKey = Routes::getGroupKey(path: $path);
        $group = Routes::getGroup(path: $path);

        if ($group === null || $groupKey === null) {
            return $this->redirectFromBase(path: $path);
        }

        $this->middlewaresResolver->processAttributeFor(className: $group, config: $this->config);

        $route = $this->getRoute(group: $group, path: $this->stripGroup(path: $path, groupKey: $groupKey));

        if ($route === null) {
            return null;
        }

        return $this->call($route);
    }

    /**
     * Перенаправить с базового пути на путь группы роутов по умолчанию
     *
     * @return null Редирект отправляется клиенту, поэтому возвращается пустое значение
     */
    private function redirectFromBase(string $path): null
    {
        if (trim($path, '/') !== '') {
            return null;
        }

        $target = Routes::getDefaultGroupPath();
        $base = rtrim((string) parse_url(Routes::getBase(), PHP_URL_PATH), '/');

        header('Location: ' . $base . $target, true, 302);

        return null;
    }

    /**
     * Исключить из пути ключ группы роутов
     *
     * Ключи роутов группы хранят путь без ключа группы, поэтому '/test/welcome'
     * при группе 'test' сопоставляется с ключом '/welcome'.
     */
    private function stripGroup(string $path, string $groupKey): string
    {
        $stripped = preg_replace('#^/' . preg_quote($groupKey, '#') . '#u', '', $path);

        return '/' . trim(is_string($stripped) ? $stripped : $path, '/');
    }

    /**
     * Получить роут группы по шаблону пути
     *
     * Переданный путь уже должен быть лишён префикса base и ключа группы.
     *
     * @param class-string $group
     * @throws \Exception
     * @throws \ReflectionException
     */
    public function getRoute(string $group, ?string $path = null): ?RouteInterface
    {
        if (!method_exists($group, 'getRoutes')) {
            return null;
        }

        /** @var array<string, array{method: string, action: mixed}> $groupRoutes */
        $groupRoutes = $group::getRoutes();

        if ($groupRoutes === []) {
            return null;
        }

        $path ??= $this->uriBuilder->buildRequestUri()->getPath();

        $pattern = $this->uriBuilder->findPattern(routes: $groupRoutes, uriPath: $path);

        if ($pattern === null) {
            return null;
        }

        $route = $groupRoutes[$pattern] ?? null;

        if ($route === null || $route['method'] !== $this->inputResolver->getRequestMethod()) {
            return null;
        }

        return $this->routeBuilder->buildRoute(
            route: $route,
            name: $pattern,
            pathParams: $this->uriBuilder->getPathParams(routePath: $pattern, uriPath: $path),
        );
    }

    /**
     * Исключить из пути префикс, назначенный в конфиге ключу base
     *
     * Префикс берётся из пути базового адреса, поэтому ключ base может содержать
     * полный URL: '/base/test/welcome' при base 'http://localhost:8000/base'.
     */
    private function stripBase(string $path): string
    {
        $basePath = trim((string) parse_url(Routes::getBase(), PHP_URL_PATH), '/');

        if ($basePath === '') {
            return $path;
        }

        $stripped = preg_replace('#^/' . preg_quote($basePath, '#') . '#u', '', $path);

        return '/' . trim(is_string($stripped) ? $stripped : $path, '/');
    }

    /**
     * Разрешить список внешних параметров роута
     *
     * @return array<string, mixed> Список внешних параметров
     * @throws \Exception
     * @throws \ReflectionException
     */
    private function getParams(ControllerRoute $route): array
    {
        $controllerName = $route->getControllerName();
        $methodName = $route->getAction();

        $params = $this->inputResolver->applyAttributeTo(className: $controllerName, methodName: $methodName);

        // Мидлвары класса и его метода разрешаются одним вызовом: отдельный вызов для
        // класса и ещё один для метода выполнял бы мидлвары класса дважды
        return $this->middlewaresResolver->processAttributeFor(
            className: $controllerName,
            methodName: $methodName,
            params: $params,
            config: $this->config,
        );
    }

    /**
     * Вызвать роут
     */
    private function call(RouteInterface $route): mixed
    {
        try {
            if ($route instanceof ControllerRoute) {
                return $route->call(params: $this->getParams(route: $route), config: $this->config);
            }

            return $route->call([]);
        } catch (\Throwable $exception) {
            // Роут возвращает void и заменяется пустым ответом, поэтому без записи
            // в лог сбой контроллера выглядел бы как пустая страница с кодом 200
            Logger::report($exception);

            return null;
        }
    }
}
