<?php

declare(strict_types=1);

namespace Framework\Services\Auth;

use config\Auth;
use config\Cookies;
use config\Routes;
use config\Session;

final class AuthManager
{
    /**
     * Авторизоваться через браузер
     */
    public function browse(array $params, string $hashedPassword): array
    {
        $path = explode('/', Routes::getUri()->getPath());

        $clientRealm = mb_ucfirst($path[1]);
        $realms      = Auth::getConfigPart('realms');

        $realm = in_array($clientRealm, $realms) ? $clientRealm : $realms[0];

        if (!isset($params['SERVER']['PHP_AUTH_USER'])) {
            try {
                throw new \Exception("401" . PHP_EOL . "Логин не передан");
            } catch (\Throwable) {
            } finally {
                header("HTTP/1.1 401 Unauthorized");
                header("WWW-Authenticate: Basic realm=\"$realm\"");

                return [];
            }
        }

        if (!password_verify((string) $params['SERVER']['PHP_AUTH_PW'], $hashedPassword)) {
            try {
                throw new \Exception("403" . PHP_EOL . "Логин или пароль неправильный");
            } catch (\Throwable) {
            } finally {
                header("HTTP/1.1 403 Forbidden");

                return [];
            }
        }

        return [
            'login' => $params['SERVER']['PHP_AUTH_USER'],
        ];
    }

    /**
     * Авторизоваться через форму
     */
    public function form(array $params, string $hashedPassword): array
    {
        $hash = Auth::hash('base', session_id());
        $sessionCookies = Session::getConfigPart('cookies');

        if (hash_equals($hash, $sessionCookies['hash'])) {
            return [
                'login' => $sessionCookies['login'],
            ];
        }

        if (!password_verify((string) $params['POST']['password'], $hashedPassword)) {
            try {
                throw new \Exception("403" . PHP_EOL . "Логин или пароль неправильный");
            } catch (\Throwable) {
            }
        }

        Session::set();
        Cookies::set(session_id());

        return [
            'login' => $params['POST']['login'],
        ];
    }

    /**
     * Авторизоваться через дайджест
     */
    public function digest(array $params): array
    {
        $secrets = Auth::getConfigPart('secrets');

        if (!hash_equals($secrets[$params['POST']['secret']], $params['POST']['key'])) {
            try {
                throw new \Exception("403" . PHP_EOL . "Секретный ключ неправильный");
            } catch (\Throwable) {
            }
        }

        return [
            'digest' => [
                'secret' => $params['POST']['secret'],
                'key'    => $params['POST']['key'],
            ],
        ];
    }
}
