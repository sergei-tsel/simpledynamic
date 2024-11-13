<?php
declare(strict_types=1);
namespace config;

/**
 * Конфигурация переменных сессии
 */
class Session extends Config
{
    protected static array  $local    = [
        'options' => [],
    ];
    protected static string $filename = '';
    protected static string $options  = '';

    /**
     * Получить переменные сессии
     */
    public static function getConfig(): array
    {
        if (self::$filename) {
            /** @var array $session */
            $session = array_merge(
                self::$local,
                yaml_parse_file(self::$filename),
            );
        } else {
            $session = self::$local;
        }

        $session['cookies'] = SessionCookies::getConfig();

        return $session;
    }

    /**
     * Установить переменные сессии
     */
    public static function setSession(?string $login = null): void
    {
        self::setConfig(function (string $key, string $value) {}, [
            'cookies' => SessionCookies::getSetMethod(),
            'options' => function (array $options) {
                session_start($options);
            },
            'params' => function (string $key, string $value) {
                $_SESSION[$key] = $value;
            },
        ], ['options']);

        if ($login) {
            $_SESSION['login'] = $login;
        }
    }
}
