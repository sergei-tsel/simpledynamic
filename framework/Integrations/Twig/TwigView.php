<?php

declare(strict_types=1);

namespace Simpledynamic\Integrations\Twig;

use config\App;
use Sympledynamic\Base\View\View;
use Twig\Environment;
use Twig\Extension\ExtensionInterface;
use Twig\Loader\FilesystemLoader;

/**
 * Представление с шаблоном Twig
 *
 * @psalm-suppress UnusedProperty
 */
final class TwigView extends View
{
    protected Environment $twig;

    public function __construct(
        protected string $template,
        protected array  $options   = [],
        protected string $path      = __DIR__ . '/../../../public/twig',
    ) {
        $this->twig = new Environment(new FilesystemLoader($path), $options);

        $extensions = App::getConfigPart('twig_extensions') ?? [];

        if (!is_array($extensions)) {
            return;
        }

        foreach ($extensions as $extension) {
            if (!is_string($extension) || !is_subclass_of($extension, ExtensionInterface::class)) {
                continue;
            }

            $this->twig->addExtension(new $extension());
        }
    }

    /**
     * Проверить существование шаблона Twig
     *
     * @psalm-suppress PossiblyUnusedMethod
     */
    public function exists(): ?View
    {
        return $this->twig->getLoader()->exists($this->template) ? $this : null;
    }

    /**
     * Загрузить и интерполировать шаблон Twig
     */
    public function render(array $data = [], ?string $blockName = null): string
    {
        $data['locale'] = App::getConfigPart('locale') ?? 'ru';

        try {
            $template = $this->twig->load($this->template);

            if ($blockName === null) {
                return $template->render($data);
            } else {
                return $template->renderBlock($blockName, $data);
            }
        } catch (\Throwable) {
            return '';
        }
    }
}
