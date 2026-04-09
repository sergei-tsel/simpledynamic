<?php

declare(strict_types=1);

namespace Sympledynamic\Services\FiberTasking;

use Fiber;

/**
 * Сервис для управления файберами
 *
 * @psalm-suppress UnusedClass
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
     */
    public function resume(string $name, mixed $value = null): mixed
    {
        /** @var Fiber $fiber */
        $fiber = $this->tasks[$name]['fiber'];

        return $fiber->resume($value);
    }

    /**
     * Возобновить выполнение файбера с передачей исключения
     */
    public function throw(string $name, \Throwable $exception): mixed
    {
        /** @var Fiber $fiber */
        $fiber = $this->tasks[$name]['fiber'];

        return $fiber->throw($exception);
    }

    /**
     * Получить значение, возвращённое файбером
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
    public function isRunning(): ?bool
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
     */
    public function suspend(mixed $value = null): mixed
    {
        try {
            return Fiber::getCurrent()?->suspend($value);
        } catch (\Throwable) {
        }

        return null;
    }

    /**
     * Выполнить задачу в файбере
     *
     * @param array{int: array{'args': array, 'func': callable}} $steps
     */
    public function performTask(array $steps): ?array
    {
        if ($steps === []) {
            return null;
        }

        $taskResult = null;

        for ($i = 0; $i < count($steps); $i++) {
            $stepResult = $steps[$i]['func'](...$steps[$i]['args']);
            $suspendParams = $this->suspend($stepResult);

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
     */
    public function execute(): void
    {
        foreach ($this->tasks as &$task) {
            /** @var Fiber $fiber */
            $fiber = $task['fiber'];

            try {
                $task['returns'][] = match (true) {
                    !$fiber->isStarted()   => $fiber->start(...$task['params']),
                    $fiber->isSuspended()  => $fiber->resume(empty($task['resumes']) ? null : array_shift($task['resumes'])),
                    $fiber->isTerminated() => $fiber->getReturn(),
                };
            } catch (\Throwable) {
            }
        }
    }

    /**
     * Удалить завершенные файберы
     */
    public function removeCompleted(): void
    {
        $this->tasks = array_filter($this->tasks, fn (array $task): bool => !$task['fiber']->isTerminated());
    }
}
