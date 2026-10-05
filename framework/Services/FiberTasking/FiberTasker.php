<?php

declare(strict_types=1);

namespace Simpledynamic\Services\FiberTasking;

use Fiber;

/**
 * Реестр задач и их планировщик
 *
 * Состояние отдельной задачи доступно через get(): FiberTask::isStarted(),
 * FiberTask::isSuspended(), FiberTask::isTerminated(), FiberTask::getReturn().
 * Выполнение по шагам и работа с текущим файбером — в TaskPipeline
 */
final class FiberTasker
{
    /** @var array<string, FiberTask> */
    private array $tasks = [];

    /**
     * Добавить новую задачу
     *
     * @param list<mixed> $params Аргументы, передаваемые в файбер при старте
     * @param list<mixed> $resumes Значения, передаваемые в файбер при каждом возобновлении
     */
    public function add(string $name, callable $task, array $params = [], array $resumes = []): void
    {
        $this->tasks[$name] = new FiberTask(
            fiber: new Fiber($task),
            params: array_values($params),
            resumes: array_values($resumes),
        );
    }

    /**
     * Проверить, зарегистрирована ли задача
     */
    public function has(string $name): bool
    {
        return array_key_exists($name, $this->tasks);
    }

    /**
     * Получить задачу по имени
     */
    public function get(string $name): ?FiberTask
    {
        return $this->tasks[$name] ?? null;
    }

    /**
     * Получить имена зарегистрированных задач
     *
     * @return list<string>
     */
    public function names(): array
    {
        return array_keys($this->tasks);
    }

    /**
     * Начать выполнение задачи
     *
     * @throws \Throwable Исключение, брошенное задачей
     */
    public function start(string $name): mixed
    {
        return $this->get($name)?->start();
    }

    /**
     * Возобновить выполнение задачи с передачей значения
     *
     * @throws \Throwable Исключение, брошенное задачей
     */
    public function resume(string $name, mixed $value = null): mixed
    {
        return $this->get($name)?->resume($value);
    }

    /**
     * Возобновить выполнение задачи с передачей исключения
     *
     * @throws \Throwable Исключение, не обработанное задачей
     */
    public function throw(string $name, \Throwable $exception): mixed
    {
        return $this->get($name)?->throw($exception);
    }

    /**
     * Выполнить добавленные задачи
     *
     * Каждая задача продвигается на один шаг: старт или возобновление.
     * Завершённые и выполняющиеся (вложенный вызов) задачи пропускаются
     *
     * @throws \Throwable Исключение, брошенное задачей
     */
    public function execute(): void
    {
        foreach ($this->tasks as $task) {
            $task->step();
        }
    }

    /**
     * Получить все значения, возвращённые задачей
     *
     * @return list<mixed>
     */
    public function getReturns(string $name): array
    {
        return $this->get($name)?->getReturns() ?? [];
    }

    /**
     * Удалить завершенные задачи
     */
    public function removeCompleted(): void
    {
        $this->tasks = array_filter($this->tasks, static fn(FiberTask $task): bool => !$task->isTerminated());
    }
}
