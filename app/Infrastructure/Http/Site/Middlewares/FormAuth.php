<?php

declare(strict_types=1);

namespace App\Infrastructure\Http\Site\Middlewares;

use App\Domain\User\Contracts\UserProviderInterface;
use App\Framework\Services\ParamsFiltration\FilterParam;
use App\Framework\Services\Routing\InputTypes;
use config\Auth;
use config\Cookies;
use config\Session;

/**
 * Базовая аутентификация и авторизация через форму
 */
class FormAuth
{
    /**
     * @throws \Exception
     */
    #[
        FilterParam(InputTypes::POST, 'login'),
        FilterParam(InputTypes::POST, 'password'),
    ]
    public function handle(array $params, UserProviderInterface $userRepository): array
    {
        $hash = Auth::hash('base', session_id());
        $sessionCookies = Session::getConfigPart('cookies');

        if (hash_equals($hash, $sessionCookies['hash'])) {
            return [
                'login' => $sessionCookies['login'],
            ];
        }

        $user = $userRepository->getOneByLogin($params['POST']['login']);

        if (password_verify((string) $params['POST']['password'], $user->getHashedPassword())) {
            Session::setSession();
            Cookies::setCookies(session_id());

            return [
                'login' => $params['POST']['login'],
            ];
        }

        throw new \Exception("403" . PHP_EOL . "Логин или пароль неправильный");
    }
}
