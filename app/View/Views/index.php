<?php

require __DIR__ . '/../../../vendor/autoload.php';

use App\Controller\Routes\Router;

$filePath = __DIR__ . $_SERVER['REQUEST_URI'];;

if (is_file($filePath)) {
    header("Content-type: " . mime_content_type($filePath) . "; charset=utf-8");

    if (str_ends_with(strtolower($filePath), ".php")) {
        include $filePath;
    } else {
        readfile($filePath);
    }

    exit;
}

Router::handle();
