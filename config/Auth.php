<?php
declare(strict_types=1);
namespace config;

use Random\Engine\Secure;

/**
 * Конфигурация авторизации
 */
class Auth extends Config
{
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
            'type'   => [
                'base',
                'file',
                'nkdf',
                'hmac',
                'hmacFile',
                'pbkdf2',
            ],
            'algo'   => 'sha256',
            'length' => 0,
            'iterations' => 600000,
        ],
        'secrets'  => [
        ],
        ''
    ];
    protected static string $filename = '';
    /**
     * Хешировать, применяя конфигурацию
     */
    public static function hash(
        string                        $type,
        #[\SensitiveParameter] string $data,
        string                        $secret = '',
        string                        $info   = '',
    ): string
    {
        $auth = self::getConfig();
        $algo = $auth['hash']['algo'];

        if ($secret) {
            $key = $auth['secrets'][$secret];
        }

        return match ($type) {
            'base'     => hash($algo, $data),
            'file'     => hash_file($algo, $data),
            'nkdf'     => hash_hkdf($algo, $key, $auth['hash']['length'], $info, (new Secure())->generate()),
            'hmac'     => hash_hmac($algo, $data, $key),
            'hmacFile' => hash_hmac_file($algo, $data, $key),
            'pbkdf2'   => hash_pbkdf2($algo, $data, (new Secure())->generate(), $auth['hash']['iterations'], $auth['hash']['length']),
        };
    }

    /**
     * Хешировать пароль, применяя конфигурацию
     */
    public static function hashPassword(#[\SensitiveParameter] string $password): string
    {
        $auth = self::getConfig();

        return password_hash($password, $auth['password']['algo'], $auth['password']['options']);
    }
}
