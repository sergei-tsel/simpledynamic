<?php

declare(strict_types=1);

namespace Sympledynamic\Services\FiberTasking;

use Fiber;

/**
 * Сервис для управления файберами
 *
 * @psalm-suppress UnusedClass
 */
final class FiberManager
{
    /** @var array<string, array{fiber: Fiber, params: array, resumes: array, returns: array}> */
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

        $fiber = $task['fiber'];

        return $fiber->start(...$task['params']);
    }

    /**
     * Возобновить выполнение файбера с передачей значения
     */
    public function resume(string $name, mixed $value = null): mixed
    {
        $fiber = $this->tasks[$name]['fiber'];

        try {
            return $fiber->resume($value);
        } catch (\Throwable) {
            return null;
        }
    }

    /**
     * Возобновить выполнение файбера с передачей исключения
     */
    public function throw(string $name, \Throwable $exception): mixed
    {
        $fiber = $this->tasks[$name]['fiber'];

        try {
            return $fiber->throw($exception);
        } catch (\Throwable) {
            return null;
        }
    }

    /**
     * Получить значение, возвращённое файбером
     */
    public function getReturn(string $name): mixed
    {
        $fiber = $this->tasks[$name]['fiber'];

        return $fiber->getReturn();
    }

    /**
     * Проверить, запушен ли файбер
     */
    public function isStarted(string $name): bool
    {
        $fiber = $this->tasks[$name]['fiber'];

        return $fiber->isStarted();
    }

    /**
     * Проверить, приостановлен ли файбер
     */
    public function isSuspended(string $name): bool
    {
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
        $fiber = $this->tasks[$name]['fiber'];

        return $fiber->isTerminated();
    }

    /**
     * Приостановить выполнение файбера, в котором вызов
     *
     * @param array|null|object|scalar $value
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
     * @param array{int: array{'args': array, 'func': callable}}|null[] $steps
     */
    public function performTask(array $steps): mixed
    {
        if ($steps === []) {
            return null;
        }

        $taskResult = null;

        /** @var array<int, array{'args': array, 'func': callable}> $steps */
        $stepsCount = count($steps);
        for ($i = 0; $i < $stepsCount; $i++) {
            /** @var object|array|scalar|null $stepResult */
            $stepResult = $steps[$i]['func'](...$steps[$i]['args']);

            /** @var object|array|scalar|null $suspendParams */
            $suspendParams = $this->suspend($stepResult) ?? null;

            $nextIndex = $i + 1;
            if (isset($steps[$nextIndex]) && $nextIndex > 0) {
                if (is_array($stepResult) && $stepResult !== []) {
                    $steps[$nextIndex]['args'] = array_merge($steps[$nextIndex]['args'], $stepResult);
                }

                if (is_array($suspendParams) && $suspendParams !== []) {
                    $steps[$nextIndex]['args'] = array_merge($steps[$nextIndex]['args'], $suspendParams);
                }
            } else {
                $taskResult = $stepResult;
            }
        }

        return $taskResult;
    }

    /**
     * Выполнить добавленные файберы
     */
    public function execute(): void
    {
        foreach ($this->tasks as &$task) {
            $fiber = $task['fiber'];

            try {
                /** @psalm-suppress MixedAssignment */
                $task['returns'][] = match (true) {
                    !$fiber->isStarted()   => $fiber->start(...$task['params']),
                    $fiber->isSuspended()  => $fiber->resume(array_shift($task['resumes'])),
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
