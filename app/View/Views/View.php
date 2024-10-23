<?php
declare(strict_types=1);
namespace App\View\Views;

/**
 * Представление с шаблоном PHP
 */
class View
{
    public function __construct(
        protected string $template,
    )
    {
    }

    /**
     * Проверить существование шаблона
     */
    public function exists(): ?View
    {
        return (
            file_exists($this->template) && (
                is_file($this->template) || is_link($this->template)
            )
        ) ? $this : null;
    }

    /**
     * Загрузить и интерполировать шаблон
     */
    public function render(array $data = []): string
    {
        extract($data);

        $template = is_link($this->template) ? readlink($this->template) : $this->template;

        return file_get_contents($template);
    }
}