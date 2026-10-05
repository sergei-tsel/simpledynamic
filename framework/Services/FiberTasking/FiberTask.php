<?php

declare(strict_types=1);

namespace Simpledynamic\Services\FiberTasking;

use Fiber;

/**
 * Зарегистрированная задача: файбер и его состояние
 */
final class FiberTask
{
    /** @var list<mixed> Значения, полученные на шагах, и результат задачи */
    private array $returns = [];

    /**
     * @param list<mixed> $params Аргументы, передаваемые в файбер при старте
     * @param list<mixed> $resumes Значения, передаваемые в файбер при каждом возобновлении
     */
    public function __construct(
        private readonly Fiber $fiber,
        private array $params = [],
        private array $resumes = [],
    ) {}

    /**
     * Начать выполнение задачи
     *
     * @throws \Throwable Исключение, брошенное задачей
     */
    public function start(): mixed
    {
        if ($this->fiber->isStarted()) {
            return null;
        }

        return $this->fiber->start(...$this->params);
    }

    /**
     * Возобновить выполнение задачи с передачей значения
     *
     * @throws \Throwable Исключение, брошенное задачей
     */
    public function resume(mixed $value = null): mixed
    {
        if (!$this->fiber->isSuspended()) {
            return null;
        }

        return $this->fiber->resume($value);
    }

    /**
     * Возобновить выполнение задачи с передачей исключения
     *
     * @throws \Throwable Исключение, не обработанное задачей
     */
    public function throw(\Throwable $exception): mixed
    {
        if (!$this->fiber->isSuspended()) {
            return null;
        }

        return $this->fiber->throw($exception);
    }

    /**
     * Продвинуть задачу на один шаг и записать результат
     *
     * В returns попадает значение приостановки, а для завершившейся задачи —
     * результат задачи, поэтому getReturn() не нужно вызывать отдельно.
     * Завершённые задачи и задачи в состоянии running (вложенный вызов)
     * не продвигаются
     *
     * @throws \Throwable Исключение, брошенное задачей
     */
    public function step(): void
    {
        $fiber = $this->fiber;

        if ($fiber->isStarted() && !$fiber->isSuspended()) {
            return;
        }

        /** @var mixed $stepValue */
        $stepValue = $fiber->isStarted()
            ? $fiber->resume(array_shift($this->resumes))
            : $fiber->start(...$this->params);

        $this->returns[] = $fiber->isTerminated() ? $fiber->getReturn() : $stepValue;
    }

    /**
     * Получить значение, возвращённое задачей
     *
     * Возвращает null, если задача ещё не завершена. Если задача завершилась
     * исключением, оно будет выброшено повторно
     */
    public function getReturn(): mixed
    {
        if (!$this->fiber->isTerminated()) {
            return null;
        }

        return $this->fiber->getReturn();
    }

    /**
     * Получить все значения, возвращённые задачей
     *
     * @return list<mixed>
     */
    public function getReturns(): array
    {
        return $this->returns;
    }

    /**
     * Проверить, запущена ли задача
     */
    public function isStarted(): bool
    {
        return $this->fiber->isStarted();
    }

    /**
     * Проверить, приостановлена ли задача
     */
    public function isSuspended(): bool
    {
        return $this->fiber->isSuspended();
    }

    /**
     * Проверить, завершена ли задача
     */
    public function isTerminated(): bool
    {
        return $this->fiber->isTerminated();
    }
}
