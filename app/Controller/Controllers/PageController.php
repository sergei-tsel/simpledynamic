<?php
declare(strict_types=1);
namespace App\Controller\Controllers;

use App\Controller\Controllers\Controller;
use App\View\Views\TwigView;

/**
 * Контроллер для страниц, доступных без авторизации
 */
class PageController extends Controller
{
    protected function welcome(): void
    {
        echo (new TwigView('welcome.twig'))->render();
    }
}
