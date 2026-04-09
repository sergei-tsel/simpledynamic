<?php

declare(strict_types=1);

// Вывод ошибок пользователю отключён
ini_set('display_errors', '0');
// Логирование ошибок включено
ini_set('log_errors', '1');
// Указан файл для фатальных ошибок
ini_set('error_log', __DIR__ . '/../logs/php_errors_' . date('Y-m-d') . '.log');

require __DIR__ . '/../vendor/autoload.php';

use config\Env;
use config\Headers;
use config\Routes;
use Sympledynamic\Services\Logging\Logger;
use Sympledynamic\Services\Routing\Router;

new Logger(__DIR__ . '/../logs/');

$filePath = __DIR__ . '/' . Routes::getUri()->getPath();

if (is_file($filePath)) {
    $contentType = mime_content_type($filePath);

    if ($contentType !== false) {
        header("Content-type: " . $contentType . "; charset=utf-8");
    }

    if (str_ends_with(strtolower($filePath), ".php")) {
        include $filePath;
    } else {
        readfile($filePath);
    }

    exit;
}

Env::set();

try {
    Router::handle();
} catch (\Throwable) {
}

Headers::set();
