<?php
declare(strict_types=1);
namespace App\Controller\Routes;

enum ParamTypes: string
{
    case POST = 'post';
    case GET = 'get';
    case COOKIE = 'cookie';
    case FILES = 'files';
    case ENV = 'env';
    case SERVER = 'server';
    case SESSION = 'session';
    case PATH = 'path';
    case CONFIG = 'config';

    /**
     * Получить целочисленный эквивалент
     */
    public function getEquivalent(): int
    {
        return match ($this) {
            ParamTypes::POST    => INPUT_POST,
            ParamTypes::GET     => INPUT_GET,
            ParamTypes::COOKIE  => INPUT_COOKIE,
            ParamTypes::FILES   => 3,
            ParamTypes::ENV     => INPUT_ENV,
            ParamTypes::SERVER  => INPUT_SERVER,
            ParamTypes::SESSION => 6,
            ParamTypes::PATH    => 7,
            ParamTypes::CONFIG  => 8,
        };
    }
}
