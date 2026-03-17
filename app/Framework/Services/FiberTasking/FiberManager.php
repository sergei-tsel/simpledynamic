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
    public function add(string $name, callable $task, array $params = [], array $resumes = []): void
    {
        $this->tasks[$name] = [
            'fiber'   => new Fiber($task),
            'params'  => $params,
            'resumes' => $resumes,
            'returns' => [],
        ];
    }

    public function getReturns(string $name): array
    {
        if (!isset($this->tasks[$name])) {
            return [];
        }

        return $this->tasks[$name]['returns'];
    }

    /**
     * Начать выполнение файбера
     *
     * @throws \Throwable
     */
    public function start(string $name): mixed
    {
        $task = $this->tasks[$name];
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
     * Проверить, работает ли файбер, в котором вызов
     */
    public function isRunning(): bool
    {
        return Fiber::getCurrent()?->isRunning();
    }

    /**
     * Проверить, завершён ли файбер
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
     * Выполнить задачу в файбере
     * @param array{int: array{'args': array, 'func': callable}} $steps
     *
     * @throws \Throwable
     */
    public function performTask(array $steps): ?array
    {
        if ($steps === []) {
            return null;
        }

        $taskResult = null;

        for ($i = 0; $i < count($steps); $i++) {
            $stepResult = $steps[$i]['func'](...$steps[$i]['args']);
            $suspendParams = Fiber::getCurrent()->suspend($stepResult);

            if (isset($steps[$i + 1])) {
                if (is_array($stepResult) && $stepResult !== []) {
                    $steps[$i + 1]['args'] = array_merge($steps[$i + 1]['args'], $stepResult);
                }

                if (is_array($suspendParams) && $suspendParams !== []) {
                    $steps[$i + 1]['args'] = array_merge($steps[$i + 1]['args'], $suspendParams);
                }
            } else {
                $taskResult = $stepResult;
            }
        };

        return $taskResult;
    }

    /**
     * Выполнить добавленные файберы
     *
     * @throws \Throwable
     */
    public function execute(): void
    {
        foreach ($this->tasks as &$task) {
            /** @var Fiber $fiber */
            $fiber = $task['fiber'];

            $task['returns'][] = match (true) {
                !$fiber->isStarted()   => $fiber->start(...$task['params']),
                $fiber->isSuspended()  => $fiber->resume(empty($task['resumes']) ? null : array_shift($task['resumes'])),
                $fiber->isTerminated() => $fiber->getReturn(),
            };
        }
    }

    /**
     * Удалить завершенные файберы
     */
    public function removeCompleted(): void
    {
        $this->tasks = array_filter($this->tasks, function ($task) {
            return !$task['fiber']->isTerminated();
        });
    }
}
