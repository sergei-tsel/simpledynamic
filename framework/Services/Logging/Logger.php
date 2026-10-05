<?php

declare(strict_types=1);

namespace Simpledynamic\Services\Logging;

/**
 * Логгер
 *
 * Экземпляр хранится статически: перехваченные исключения записываются через
 * статический Logger::report() из любого места фреймворка. Локальная переменная
 * с логгером в точке catch отсутствует, а внедрять зависимость в каждый класс,
 * который перехватывает исключение, означало бы связать логирование с контейнером.
 * Хранение экземпляра повторяет подход ServiceContainer::getInstance().
 */
final class Logger
{
    private static ?self $instance = null;

    public function __construct(
        private readonly string $logDir,
    ) {
        if (!is_dir($this->logDir)) {
            mkdir($this->logDir, 0o755, true);
        }

        self::$instance = $this;

        set_error_handler($this->handleError(...));
        set_exception_handler($this->handleException(...));
    }

    /**
     * Получить текущий экземпляр логгера
     */
    public static function getInstance(): ?self
    {
        return self::$instance;
    }

    /**
     * Установить экземпляр логгера
     */
    public static function setInstance(?self $logger): void
    {
        self::$instance = $logger;
    }

    /**
     * Записать перехваченное исключение в лог
     *
     * Единственная точка записи для исключений, перехваченных в catch: без неё
     * проглоченное исключение не оставляет следов. Если логгер не инициализирован,
     * запись уходит в стандартный лог PHP, заданный директивой error_log.
     */
    public static function report(\Throwable $exception): void
    {
        $instance = self::$instance;

        if ($instance === null) {
            error_log(self::format(
                ErrorLevel::EXCEPTION,
                $exception->getMessage(),
                $exception->getFile(),
                $exception->getLine(),
            ));

            return;
        }

        $instance->log(ErrorLevel::EXCEPTION, $exception->getMessage(), $exception->getFile(), $exception->getLine());
    }

    /**
     * Обработать исключение
     */
    public function handleException(\Throwable $exception): void
    {
        self::report($exception);
    }

    /**
     * Обработать ошибку
     */
    public function handleError(int $severity, string $message, string $file, int $line): bool
    {
        if (!(error_reporting() & $severity)) {
            return false;
        }

        $this->log(ErrorLevel::tryFrom($severity) ?? ErrorLevel::UNKNOWN, $message, $file, $line);

        return true;
    }

    /**
     * Записать лог в файл
     */
    private function log(ErrorLevel $errorLevel, string $message, string $file, int $line): void
    {
        $logFile = $this->logDir . '/error_' . date('Y-m-d') . '.log';

        error_log(self::format($errorLevel, $message, $file, $line), 3, $logFile);
    }

    /**
     * Собрать строку записи лога
     */
    private static function format(ErrorLevel $errorLevel, string $message, string $file, int $line): string
    {
        return sprintf("[%s] %s: %s в %s на строке %d\n", date('H:i:s e'), $errorLevel->name, $message, $file, $line);
    }
}
