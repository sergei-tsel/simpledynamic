<?php

declare(strict_types=1);

namespace App\Infrastructure\Http\Site\Middlewares;

use App\Domain\User\Contracts\UserProviderInterface;
use App\Framework\Services\ParamsFiltration\FilterParam;
use App\Framework\Services\Routing\InputTypes;
use config\Auth;
use config\Routes;

/**
 * Базовая браузерная аутентификация
 */
class BrowserAuth
{
    /**
     * @return array{login?: string}
     */
    #[
        FilterParam(InputTypes::SERVER, 'PHP_AUTH_USER'),
        FilterParam(InputTypes::SERVER, 'PHP_AUTH_PW'),
    ]
    public function handle(array $params, UserProviderInterface $userRepository): array
    {
        $path = explode('/', Routes::getUri()->getPath());

        $clientRealm = mb_ucfirst($path[1]);
        $realms      = Auth::getConfigPart('realms');

        $realm = in_array($clientRealm, $realms) ? $clientRealm : $realms[0];

        if (!isset($params['SERVER']['PHP_AUTH_USER'])) {
            header("HTTP/1.1 401 Unauthorized");
            header("WWW-Authenticate: Basic realm=\"$realm\"");
        }

        $user = $userRepository->getOneByLogin($params['SERVER']['PHP_AUTH_USER']);

        if (password_verify((string) $params['SERVER']['PHP_AUTH_PW'], $user->getHashedPassword())) {
            return [
                'login' => $params['SERVER']['PHP_AUTH_USER'],
            ];
        }

        header("HTTP/1.1 403 Forbidden");

        return [];
    }
}
