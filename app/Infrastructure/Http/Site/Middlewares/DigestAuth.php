<?php

declare(strict_types=1);

namespace App\Infrastructure\Http\Site\Middlewares;

use App\Framework\Services\ParamsFiltration\FilterParam;
use App\Framework\Services\Routing\InputTypes;
use config\Auth;

/**
 * Базовая авторизация для обмена сообщениями
 */
class DigestAuth
{
    /**
     * @return array{digest: mixed}
     * @throws \Exception
     */
    #[
        FilterParam(InputTypes::POST, 'secret'),
        FilterParam(InputTypes::POST, 'key'),
    ]
    public function handle(array $params): array
    {
        $secrets = Auth::getConfigPart('secrets');

        if (hash_equals($secrets[$params['POST']['secret']], $params['POST']['key'])) {
            return [
                'digest' => [
                    'secret' => $params['POST']['secret'],
                    'key'    => $params['POST']['key'],
                ],

            ];
        }

        throw new \Exception("403" . PHP_EOL . "Секретный ключ сообщения неправильный");
    }
}
