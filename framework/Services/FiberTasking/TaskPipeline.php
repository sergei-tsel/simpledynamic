<?php

declare(strict_types=1);

namespace Simpledynamic\Services\FiberTasking;

use Fiber;

/**
 * Выполнение задачи по шагам и работа с текущим файбером
 */
final class TaskPipeline
{
    /**
     * Выполнить задачу по шагам
     *
     * В аргументы следующего шага мержится результат предыдущего шага и значение,
     * полученное при возобновлении, — но только если они массивы. Вне файбера
     * suspend() не срабатывает и шаги выполняются подряд. Шаги без корректного
     * func пропускаются, ключи шагов значения не имеют
     *
     * @param array<array-key, mixed> $steps Шаги вида ['func' => callable, 'args' => array]
     * @throws \Throwable Файбер не может быть приостановлен
     */
    public function performTask(array $steps): mixed
    {
        $taskList = $this->normalizeSteps($steps);

        if ($taskList === []) {
            return null;
        }

        $taskResult = null;
        $stepsCount = count($taskList);

        for ($i = 0; $i < $stepsCount; $i++) {
            $step = $taskList[$i] ?? null;

            if ($step === null) {
                continue;
            }

            /** @var mixed $stepResult */
            $stepResult = $step['func'](...$step['args']);

            /** @var mixed $suspendedValue */
            $suspendedValue = $this->suspend($stepResult);

            $nextIndex = $i + 1;
            $nextStep = $taskList[$nextIndex] ?? null;

            if ($nextStep === null) {
                /** @var mixed $taskResult */
                $taskResult = $stepResult;

                continue;
            }

            $nextStep['args'] = $this->mergeArgs($nextStep['args'], $stepResult);
            $nextStep['args'] = $this->mergeArgs($nextStep['args'], $suspendedValue);

            $taskList[$nextIndex] = $nextStep;
        }

        return $taskResult;
    }

    /**
     * Приостановить выполнение файбера, в котором вызов
     *
     * Вне файбера значение игнорируется и возвращается null
     *
     * @throws \Throwable Файбер не может быть приостановлен
     */
    public function suspend(mixed $value = null): mixed
    {
        return Fiber::getCurrent()?->suspend($value);
    }

    /**
     * Проверить, работает ли файбер, в котором вызов
     */
    public function isRunning(): bool
    {
        return Fiber::getCurrent()?->isRunning() ?? false;
    }

    /**
     * Привести шаги к списку с обязательным func и списком аргументов
     *
     * @param array<array-key, mixed> $steps
     * @return list<array{args: list<mixed>, func: callable}>
     */
    private function normalizeSteps(array $steps): array
    {
        $taskList = [];

        /** @var mixed $step */
        foreach ($steps as $step) {
            /** @var mixed $func */
            $func = is_array($step) ? $step['func'] ?? null : null;

            if (!is_callable($func)) {
                continue;
            }

            $args = is_array($step['args'] ?? null) ? array_values($step['args']) : [];

            $taskList[] = ['args' => $args, 'func' => $func];
        }

        return $taskList;
    }

    /**
     * Дописать значение в аргументы следующего шага, если это непустой массив
     *
     * @param list<mixed> $args
     * @return list<mixed>
     */
    private function mergeArgs(array $args, mixed $value): array
    {
        if (!is_array($value) || $value === []) {
            return $args;
        }

        return array_values(array_merge($args, $value));
    }
}
