<?php

declare(strict_types=1);

namespace config;

use Random\Engine\Secure;

/**
 * Конфигурация авторизации
 *
 * @api
 * @psalm-suppress ClassCanBeFinal
 */
class Auth extends Config
{
    /**
     * @var array<string, array<array-key, mixed>|scalar|null>
     * @psalm-suppress InvalidAttribute
     */
    #[\Override]
    protected static array $local = [
        'realms'   => [
            'User',
            'Admin',
        ],
        'password' => [
            'algo'       => PASSWORD_DEFAULT,
            'options'    => [
                'cost' => 12,
            ],
        ],
        'hash'     => [
            'type'       => [
                'base',
                'file',
                'nkdf',
                'hmac',
                'hmacFile',
                'pbkdf2',
            ],
            'algo'       => 'sha256',
            'length'     => 0,
            'iterations' => 600000,
        ],
        'secrets'  => [
        ],
    ];

    /**
     * @psalm-suppress InvalidAttribute
     */
    #[\Override]
    protected static string $filename = '';

    /**
     * Хешировать, применяя конфигурацию
     */
    public static function hash(
        string $type,
        #[\SensitiveParameter]
        string $data,
        string $secret = '',
        string $info   = '',
    ): string {
        /** @var array{type: string[], algo: string, iterations: int, length: int} $auth */
        $auth = self::getConfigPart('auth');

        if (!in_array($type, $auth)) {
            return '';
        }

        /** @var string[] $secrets */
        $secrets = self::getConfigPart('secrets');

        return (string) match ($type) {
            'base'     => hash(algo: $auth['algo'], data: $data),
            'file'     => hash_file(algo: $secrets['algo'], filename: $data),
            'nkdf'     => hash_hkdf(algo: $auth['algo'], key: $secrets[$secret], length: $auth['length'], info: $info, salt: new Secure()->generate()),
            'hmac'     => hash_hmac(algo: $auth['algo'], data: $data, key: $secrets[$secret]),
            'hmacFile' => hash_hmac_file(algo: $auth['algo'], filename: $data, key: $secrets[$secret]),
            'pbkdf2'   => hash_pbkdf2(algo: $auth['algo'], password: $data, salt: new Secure()->generate(), iterations: $auth['iterations'], length: $auth['length']),
            default    => '',
        };
    }

    /**
     * Хешировать пароль, применяя конфигурацию
     *
     * @psalm-suppress PossiblyUnusedMethod
     */
    public static function hashPassword(#[\SensitiveParameter] string $password): string
    {
        /** @var array{algo: string, options: array{string, scalar}} $auth */
        $auth = self::getConfigPart('password');

        return password_hash($password, $auth['algo'], $auth['options']);
    }
}
