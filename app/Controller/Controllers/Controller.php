<?php

declare(strict_types=1);

namespace App\Controller\Controllers;

use Fiber;

/**
 * Базовый контроллер
 */
abstract class Controller
{
    private array $tasks = [];
    private bool $running = true;

    public function addTask(callable $task): void
    {
        $this->tasks[] = new Fiber(function () use ($task) {
            try {
                $task();
            } catch (\Throwable $exception) {
                echo "Ошибка в задаче: {$exception->getMessage()}\n";
            }
        });
    }

    public function run(): void
    {
        while (!empty($this->tasks)) {
            /** @var Fiber $fiber */
            foreach ($this->tasks as $index => $fiber) {
                try {
                    if (!$fiber->isStarted()) {
                        $fiber->start();
                    } else {
                        $fiber->resume();
                    }
                } catch (\Throwable $exception) {
                   $fiber->throw($exception);
                }

                if ($fiber->isTerminated()) {
                    unset($this->tasks[$index]);
                }
            }

            usleep(1);
        }

        $this->running = false;
    }

    public function isRunning(): bool
    {
        return $this->running;
    }
}
