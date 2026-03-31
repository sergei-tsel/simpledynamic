<?php

declare(strict_types=1);

namespace App\Infrastructure\Gateway\Templating;

use config\App;
use Twig\Environment;
use Twig\Loader\FilesystemLoader;

/**
 * Представление с шаблоном Twig
 */
class TwigView extends View
{
    protected Environment $twig;

    public function __construct(
        protected string $template,
        protected array  $options   = [],
        protected string $path      = __DIR__ . '/../../../../public/twig',
    ) {
        $this->twig = new Environment(new FilesystemLoader($path), $options);

        $extensions = App::getConfigPart('twig_extensions') ?? [];

        if ($extensions !== []) {
            foreach ($extensions as $extension) {
                $this->twig->addExtension(new $extension());
            }
        }

        parent::__construct($template);
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
        $data['locale'] = App::getConfigPart('locale') ?? 'ru';

        $template = $this->twig->load($this->template);

        if ($blockName === null) {
            return $template->render($data);
        } else {
            return $template->renderBlock($blockName, $data);
        }
    }
}
