<?php

declare(strict_types=1);

namespace Simpledynamic\Integrations\Twig;

use Simpledynamic\Base\View\ViewInterface;
use Simpledynamic\Services\Configuration\App;
use Simpledynamic\Services\Logging\Logger;
use Twig\Environment;
use Twig\Extension\ExtensionInterface;
use Twig\Loader\FilesystemLoader;

/**
 * Представление с шаблоном Twig
 */
final class TwigView implements ViewInterface
{
    protected Environment $twig;

    /**
     * @param array<string, mixed> $options Настройки Twig
     * @param string|null $path Каталог шаблонов, если не задан в конфигурации
     * @throws \ReflectionException
     */
    public function __construct(
        protected string $template,
        array $options = [],
        ?string $path = null,
    ) {
        $this->twig = new Environment(new FilesystemLoader($path ?? self::getPath()), $options);

        /**
         * @var array<int, class-string|mixed>|mixed $extensions
         */
        $extensions = App::getConfigPart('twig_extensions') ?? [];

        if (!is_array($extensions)) {
            return;
        }

        foreach ($extensions as $extension) {
            if (!is_string($extension) || !is_subclass_of($extension, ExtensionInterface::class)) {
                continue;
            }

            $reflection = new \ReflectionClass($extension);

            if (!$reflection->isInstantiable()) {
                continue;
            }

            $this->twig->addExtension($reflection->newInstance());
        }
    }

    /**
     * Получить каталог шаблонов из конфигурации
     */
    private static function getPath(): string
    {
        /**
         * @var string $path
         */
        $path = App::getConfigPart('twig_path') ?? '';

        return $path !== '' ? $path : __DIR__ . '/../../../../../../public/twig';
    }

    /**
     * Проверить существование шаблона Twig
     */
    #[\Override]
    public function exists(): ?TwigView
    {
        return $this->twig->getLoader()->exists($this->template) ? $this : null;
    }

    /**
     * Загрузить и интерполировать шаблон Twig
     */
    #[\Override]
    public function render(array $data = [], ?string $blockName = null): string
    {
        /**
         * @var string $locale
         */
        $locale = App::getConfigPart('locale') ?? 'ru';
        array_push($data, $locale);

        try {
            $template = $this->twig->load($this->template);

            if ($blockName !== null) {
                return $template->renderBlock($blockName, $data);
            }

            return $template->render($data);
        } catch (\Throwable $exception) {
            // Метод возвращает string, поэтому сбой шаблона иначе выглядел бы как
            // пустая страница: синтаксическая ошибка в шаблоне осталась бы незаметной
            Logger::report($exception);

            return '';
        }
    }
}
