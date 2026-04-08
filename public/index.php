<?php

declare(strict_types=1);

require __DIR__ . '/../vendor/autoload.php';

use config\Env;
use config\Headers;
use config\Routes;
use Framework\Services\Routing\Router;

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
