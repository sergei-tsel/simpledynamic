<?php

declare(strict_types=1);

namespace App\Infrastructure\Http\Site\Middlewares;

use App\Domain\User\Contracts\UserProviderInterface;
use App\Framework\Services\Routing\Param;
use App\Framework\Services\Routing\ParamTypes;
use config\Auth;
use config\Cookies;
use config\Session;

/**
 * Базовая аутентификация и авторизация через форму
 */
#[
    Param(ParamTypes::CONFIG, 'session', Cookies::class),
    Param(ParamTypes::CONFIG, 'login', Session::class),
    Param(ParamTypes::POST, 'login'),
    Param(ParamTypes::POST, 'password'),
]
class FormAuth
{
    public function handle(array $params, UserProviderInterface $userRepository): array
    {
        $hash = Auth::hash('base', session_id());

        if (hash_equals($hash, $params['config']['Cookies']['session'])) {
            return [
                $params['config']['Cookies']['login'],
            ];
        }

        if ($this->checkPassword($userRepository, $params['post']['login'], $params['post']['password'])) {
            Session::setSession();
            Cookies::setCookies(session_id());

            return [
                'login' => $params['post']['login'],
            ];
        }

        throw new \Exception("403" . PHP_EOL . "Логин или пароль неправильный");
    }


    /**
     * Проверить пароль
     */
    protected function checkPassword(UserProviderInterface $userRepository, string $login, #[\SensitiveParameter] string $password): bool
    {
        $user = $userRepository->getOneByLogin($login);

        return password_verify($password, $user->getHashedPassword());
    }
}
