<?php

require __DIR__ . '/../vendor/autoload.php';

use App\Framework\Services\Routing\Router;
use config\Routes;

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

Router::handle();
