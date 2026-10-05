<?php

declare(strict_types=1);

namespace Test\App\Http\Controllers;

use Simpledynamic\Base\Controller\ControllerInterface;
use Simpledynamic\Integrations\Twig\TwigView;
use Simpledynamic\Services\Filtration\FilterParam;
use Simpledynamic\Services\Routing\InputType;
use Simpledynamic\Services\Routing\Middleware;
use Test\App\Http\Middlewares\ControllerTest;
use Test\App\Http\Middlewares\MethodTest;

/**
 * Тестовый контроллер
 */
#[Middleware(ControllerTest::class)]
class TestController implements ControllerInterface
{
    /**
     * Вернуть представление welcome
     *
     * Роут с путём '/' группы: на него выполняется перенаправление с базового пути.
     * Экшену не нужны внешние параметры, поэтому request не объявляется.
     *
     * @throws \ReflectionException
     */
    public function root(): void
    {
        echo new TwigView(template: 'welcome.php.twig')->render();
    }

    /**
     * Поприветствовать
     *
     * @param array<string, mixed> $request Внешние параметры по типам
     * @throws \ReflectionException
     */
    #[Middleware(name: MethodTest::class)]
    public function welcome(array $request): void
    {
        if (($request['controller'] ?? null) !== true || ($request['method'] ?? null) !== true) {
            return;
        }

        echo new TwigView(template: 'welcome.php.twig')->render();
    }

    /**
     * Показать GET-параметр из request и отдельный PATH-параметр
     *
     * @param array<string, mixed> $request Внешние параметры по типам
     */
    #[FilterParam(type: InputType::GET, varName: 'query')]
    public function pathParam(array $request, string $pathParamName): void
    {
        echo
            'pathParam:'
                . $pathParamName
                . ';get:'
                . (string) ($request['GET']['query'] ?? 'missing')
                . ';request:'
                . (string) json_encode($request, JSON_UNESCAPED_UNICODE);
    }

    /**
     * Поприветствовать по имени из PATH
     *
     * PATH-параметр приходит отдельным аргументом, внешние параметры методу не нужны.
     */
    public function greeting(string $name): void
    {
        echo 'greeting:' . $name;
    }
}
