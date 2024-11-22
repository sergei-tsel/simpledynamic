<?php

declare(strict_types=1);

namespace App\View\Views;

use config\App;
use Twig\Environment;
use Twig\Loader\FilesystemLoader;

/**
 * Представление с шаблном Twig
 */
class TwigView extends View
{
    public function __construct(
        protected string      $template,
        protected Environment $twig      = new Environment(new FilesystemLoader(__DIR__)),
        protected array       $options   = [],
        protected string      $path      = __DIR__,
    ) {
        if ($options !== [] || $path !== __DIR__) {
            $this->twig = new Environment(new FilesystemLoader($path), $options);
        }

        parent::__construct($path . '\\' . $template);
    }

    /**
     * Проверить существование шаблона Twig
     */
    #[\Override]
    public function exists(): ?View
    {
        return $this->twig->getLoader()->exists($this->template) ? $this : null;
    }

    /**
     * Загрузить и интерполировать шаблон Twig
     */
    #[\Override]
    public function render(array $data = [], ?string $blockName = null): string
    {
        $appConfig      = App::getConfig();
        $data['locale'] = $appConfig['locale'];

        $template = $this->twig->load($this->template);

        if ($blockName === null) {
            return $template->render($data);
        } else {
            return $template->renderBlock($blockName, $data);
        }
    }
}
