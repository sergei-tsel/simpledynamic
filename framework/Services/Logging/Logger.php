<?php

declare(strict_types=1);

namespace Simpledynamic\Services\Logging;

/**
 * Логгер
 *
 * @psalm-suppress UnusedClass
 */
final readonly class Logger
{
    public function __construct(
        private string $logDir,
    ) {
        if (!is_dir($this->logDir)) {
            mkdir($this->logDir, 0o755, true);
        }

        set_error_handler($this->handleError(...));
        set_exception_handler($this->handleException(...));
    }



    /**
     * Обработать исключение
     */
    public function handleException(\Throwable $exception): void
    {
        $this->log(
            ErrorLevel::EXCEPTION,
            $exception->getMessage(),
            $exception->getFile(),
            $exception->getLine()
        );
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

        $formattedMessage = sprintf(
            "[%s] %s: %s в %s на строке %d\n",
            date('H:i:s e'),
            $errorLevel->name,
            $message,
            $file,
            $line,
        );

        error_log($formattedMessage, 3, $logFile);
    }
}
