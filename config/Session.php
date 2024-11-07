<?php
declare(strict_types=1);
namespace config;

/**
 * Конфигурация переменных сессии
 */
class Session extends Config
{
    protected static array  $local    = [];
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
    public static function setSession(): void
    {
        self::setConfig(function (string $key, string $value) {},  [
            'cookies' => SessionCookies::getSetMethod(),
            'options' => function (string $key, string $value) {
                session_start([
                    $key => $value,
                ]);
            },
            'local'   => function (string $key, string $value) {
                $_SESSION[$key] = $value;
            },
        ]);
    }
}
