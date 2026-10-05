<?php

declare(strict_types=1);

namespace Test\Config;

use Random\Engine\Secure;
use Simpledynamic\Services\Configuration\Config;

/**
 * Конфигурация авторизации
 */
class Auth extends Config
{
    /**
     * @var array<string, array<array-key, mixed>|scalar|null>
     */
    #[\Override]
    protected static array $local = [
        'realms' => [
            'User',
            'Admin',
        ],
        'password' => [
            'algo' => PASSWORD_DEFAULT,
            'options' => [
                'cost' => 12,
            ],
        ],
        'hash' => [
            'type' => [
                'base',
                'file',
                'nkdf',
                'hmac',
                'hmacFile',
                'pbkdf2',
            ],
            'algo' => 'sha256',
            'length' => 0,
            'iterations' => 600_000,
        ],
        'secrets' => [],
    ];

    #[\Override]
    protected static string $filename = '';

    /**
     * Хешировать, применяя конфигурацию
     */
    public static function hash(
        string $type,
        #[\SensitiveParameter]
        string $data,
        #[\SensitiveParameter]
        string $secret = '',
        string $info = '',
    ): string {
        /** @var array{type: string[], algo: string, iterations: positive-int, length: positive-int} $auth */
        $auth = self::getConfigPart('auth');

        if (!in_array($type, $auth, true)) {
            return '';
        }

        /** @var array{algo: string, ...<string, string>} $secrets */
        $secrets = self::getConfigPart('secrets');
        $secretValue = $secrets[$secret] ?? '';
        $key = $secretValue === '' ? null : $secretValue;

        return (string) match ($type) {
            'base' => hash(algo: $auth['algo'], data: $data),
            'file' => hash_file(algo: $secrets['algo'], filename: $data),
            'nkdf' => $key === null
                ? ''
                : hash_hkdf(
                    algo: $auth['algo'],
                    key: $key,
                    length: $auth['length'],
                    info: $info,
                    salt: new Secure()->generate(),
                ),
            'hmac' => $key === null ? '' : hash_hmac(algo: $auth['algo'], data: $data, key: $key),
            'hmacFile' => $key === null ? '' : hash_hmac_file(algo: $auth['algo'], filename: $data, key: $key),
            'pbkdf2' => hash_pbkdf2(
                algo: $auth['algo'],
                password: $data,
                salt: new Secure()->generate(),
                iterations: $auth['iterations'],
                length: $auth['length'],
            ),
            default => '',
        };
    }

    /**
     * Хешировать пароль, применяя конфигурацию
     */
    public static function hashPassword(#[\SensitiveParameter] string $password): string
    {
        /** @var array{algo: string, options: array{string, scalar}} $auth */
        $auth = self::getConfigPart('password');

        return password_hash($password, $auth['algo'], $auth['options']);
    }
}
