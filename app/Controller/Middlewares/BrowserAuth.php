<?php
declare(strict_types=1);
namespace App\Controller\Middlewares;

use App\Controller\Routes\Param;
use App\Controller\Routes\ParamTypes;
use App\Model\ORM\Repositories\UserRepository;
use config\Auth;

/**
 * Базовая браузерная аутентификация
 */
#[
    Param(ParamTypes::SERVER, 'REQUEST_URI'),
    Param(ParamTypes::CONFIG, 'realms', Auth::class),
    Param(ParamTypes::SERVER, 'PHP_AUTH_USER'),
    Param(ParamTypes::SERVER, 'PHP_AUTH_PW'),
]
class BrowserAuth
{
    public function handle(array $params): array
    {
        $url  = parse_url($params['server']['REQUEST_URI']);
        $path = explode('/', $url['path']);

        $clientRealm = mb_ucfirst($path[1]);
        $realms      = $params['config']['Auth']['realms'];

        $realm = in_array($clientRealm, $realms) ? $clientRealm : $realms[0];

        if (!isset($_SERVER['PHP_AUTH_USER'])) {
            header("HTTP/1.1 401 Unauthorized");
            header("WWW-Authenticate: Basic realm=\"$realm\"");
        }

        if ($this->checkPassword($params['server']['PHP_AUTH_USER'], $params['server']['PHP_AUTH_PW'])) {
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
    protected function checkPassword(string $login, #[\SensitiveParameter] string $password): bool
    {
        $hash = (new UserRepository())->getPasswordByLogin($login);

        return password_verify($password, $hash);
    }
}
