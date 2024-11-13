<?php
declare(strict_types=1);
namespace App\Controller\Middlewares;

use App\Controller\Routes\Param;
use App\Controller\Routes\ParamTypes;
use App\Model\ORM\Repositories\UserRepository;
use config\Auth;
use config\Cookies;
use config\Session;
use Exception;

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
    public function handle(array $params): array
    {
        $hash = Auth::hash('base', session_id());

        if (hash_equals($hash, $params['config']['Cookies']['session'])) {
            return [
                $params['config']['Cookies']['login'],
            ];
        }

        if ($this->checkPassword($params['post']['login'], $params['post']['password'])) {
            Session::setSession();
            Cookies::setCookies(session_id());

            return [
                'login' => $params['post']['login'],
            ];
        }

        throw new Exception("403" . PHP_EOL . "Логин или пароль неправильный");
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
