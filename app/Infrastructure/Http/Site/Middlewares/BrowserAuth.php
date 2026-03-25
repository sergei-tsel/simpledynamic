<?php

declare(strict_types=1);

namespace App\Infrastructure\Http\Site\Middlewares;

use App\Domain\User\Contracts\UserProviderInterface;
use App\Framework\Services\Routing\Param;
use App\Framework\Services\Routing\ParamTypes;
use config\Auth;
use config\Routes;

/**
 * Базовая браузерная аутентификация
 */
#[
    Param(ParamTypes::CONFIG, 'realms', Auth::class),
    Param(ParamTypes::SERVER, 'PHP_AUTH_USER'),
    Param(ParamTypes::SERVER, 'PHP_AUTH_PW'),
]
class BrowserAuth
{
    /**
     * @return array{login?: string}
     */
    public function handle(array $params, UserProviderInterface $userRepository): array
    {
        $path = explode('/', Routes::getUri()->getPath());

        $clientRealm = mb_ucfirst($path[1]);
        $realms      = $params['config']['Auth']['realms'];

        $realm = in_array($clientRealm, $realms) ? $clientRealm : $realms[0];

        if (!isset($params['server']['PHP_AUTH_USER'])) {
            header("HTTP/1.1 401 Unauthorized");
            header("WWW-Authenticate: Basic realm=\"$realm\"");
        }

        if ($this->checkPassword($userRepository, $params['server']['PHP_AUTH_USER'], $params['server']['PHP_AUTH_PW'])) {
            return [
                'login' => $params['server']['PHP_AUTH_USER'],
            ];
        }

        header("HTTP/1.1 403 Forbidden");

        return [];
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
