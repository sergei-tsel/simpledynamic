<?php
declare(strict_types=1);
namespace App\Controller\Middlewares;

use config\Auth;
use Exception;

/**
 * Базовая авторизация для обмена сообщениями
 */
class DigestAuth
{
    public function handle(array $params): array
    {
        $config = Auth::getConfig();
        $digest = $params['handle']['digest'];

        if (hash_equals($config['secrets'][$digest['secret']], $digest['key'])) {

            return [
                'digest' => $digest,
            ];
        }

        throw new Exception("403" . PHP_EOL . "Секретный ключ сообщения неправильный");
    }
}
