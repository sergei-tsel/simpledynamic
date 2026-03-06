<?php

declare(strict_types=1);

namespace App\Framework\Services\FiberTasking;

use Fiber;

/**
 * Сервис для управления файберами
 */
class FiberManager
{
    private array $tasks = [];

    /**
     * Добавить новый файбер
     */
    public function add(string $name, callable $task, array $params = []): void
    {
        $this->tasks[$name] = [
            'fiber'   => new Fiber($task),
            'params'  => $params,
            'returns' => [],
        ];
    }

    /**
     * Начать выполнение файбера
     *
     * @throws \Throwable
     */
    public function start(string $name): mixed
    {
        $task = $this->tasks[$name]['fiber'];
        /** @var Fiber $fiber */
        $fiber = $task['fiber'];

        return $fiber->start(...$task['params']);
    }

    /**
     * Возобновить выполнение файбера с передачей значения
     * @throws \Throwable
     */
    public function resume(string $name, mixed $value = null): mixed
    {
        /** @var Fiber $fiber */
        $fiber = $this->tasks[$name]['fiber'];

        return $fiber->resume($value);
    }

    /**
     * Возобновить выполнение файбера с передачей исключения
     * @throws \Throwable
     */
    public function throw(string $name, \Throwable $exception): mixed
    {
        /** @var Fiber $fiber */
        $fiber = $this->tasks[$name]['fiber'];

        return $fiber->throw($exception);
    }

    /**
     * Получить значение, возвращённое файбером
     * @throws \Throwable
     */
    public function getReturn(string $name): mixed
    {
        /** @var Fiber $fiber */
        $fiber = $this->tasks[$name]['fiber'];

        return $fiber->getReturn();
    }

    /**
     * Проверить, запушен ли файбер
     */
    public function isStarted(string $name): bool
    {
        /** @var Fiber $fiber */
        $fiber = $this->tasks[$name]['fiber'];

        return $fiber->isStarted();
    }

    /**
     * Проверить, приостановлен ли файбер
     */
    public function isSuspended(string $name): bool
    {
        /** @var Fiber $fiber */
        $fiber = $this->tasks[$name]['fiber'];

        return $fiber->isSuspended();
    }

    /**
     * Проверить, работает ли файбер
     */
    public function isRunning(string $name): bool
    {
        /** @var Fiber $fiber */
        $fiber = $this->tasks[$name]['fiber'];

        return $fiber->isRunning();
    }

    /**
     * Проверить, запушен ли файбер
     */
    public function isTerminated(string $name): bool
    {
        /** @var Fiber $fiber */
        $fiber = $this->tasks[$name]['fiber'];

        return $fiber->isTerminated();
    }

    /**
     * Приостановить выполнение файбера, в котором вызов
     * @throws \Throwable
     */
    public function suspend(mixed $value = null): mixed
    {
        return Fiber::getCurrent()?->suspend($value);
    }

    /**
     * Выполнить добавленные файберы
     *
     * @throws \Throwable
     */
    public function execute(array $resumeParams = []): void
    {
        foreach ($this->tasks as $name => $task) {
            try {
                /** @var Fiber $fiber */
                $fiber = $this->tasks['fiber'];

                $task['returns'][] = match (true) {
                    !$fiber->isStarted()   => $fiber->start(...$task['params']),
                    $fiber->isSuspended()  => $fiber->resume(empty($resumeParams[$name]) ? null : array_shift($resumeParams[$name])),
                    $fiber->isTerminated() => $fiber->getReturn(),
                };
            } catch (\Throwable $e) {
                $task['returns'][] = $e;
            }
        }
    }

    /**
     * Удалить завершенные файберы
     */
    public function removeCompleted(): void
    {
        $this->tasks = array_filter($this->tasks, function ($fiber) {
            return !$fiber->isTerminated();
        });
    }
}
