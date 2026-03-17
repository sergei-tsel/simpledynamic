<?php

declare(strict_types=1);

namespace App\Infrastructure\Console;

use App\Framework\Services\CLI\Command;
use App\Framework\Services\FiberTasking\FiberManager;

/**
 * Команда для тестирования управления файберами
 */
class FiberTaskingTest extends Command
{
    protected string $signature = 'app/Framework/Services/CLI/exec.php test';
    protected string $description = 'Тестирует управления файберами';

    public function __construct(
        protected FiberManager $fiberManager,
    ) {
    }

    /**
     * @throws \Throwable
     */
    public function handle(): void
    {
        echo 'Управление файберами' . PHP_EOL;

        $params = [
            'firstParam'  => 4,
            'secondParam' => 2,
        ];

        $expected = [
            [
                'secondParam' => 2,
            ],
            'Тестовое исключение',
            2,
        ];

        $taskName = 'test_task';
        $this->fiberManager->add($taskName, $this->fiberManager->performTask(...), [
            'steps' => [
                [
                    'args'  => [],
                    'func'  => $this->giveException(...),
                ],
                [
                    'args'  => [],
                    'func'  => $this->giveValue(...),
                ],
                [
                    'args' => [
                        'firstParam' => $params['firstParam'],
                    ],
                    'func' => $this->divide(...),
                ],
            ],
        ], [
            null,
            $params['secondParam'],
        ]);

        for ($i = 0; $i < 4; $i++) {
            $this->fiberManager->add($taskName . $i + 1, $this->giveException(...));
        }

        $this->manageFibers($expected, $taskName);
    }

    /**
     * Вернуть текст полученного исключения
     */
    private function giveException(): ?string
    {
        try {
            $this->fiberManager->suspend();
        } catch (\Throwable $exception) {
            echo '* Возобновление с передачей исключения' . PHP_EOL;
            return $exception->getMessage();
        }

        return null;
    }

    /**
     * Вернуть полученное значение
     *
     * @throws \Throwable
     */
    private function giveValue(): array
    {
        $param ??= $this->fiberManager->suspend();

        if ($this->fiberManager->isRunning()) {
            echo '* Возобновление с передачей значения' . PHP_EOL;
        }

        return [
            'secondParam' => $param,
        ];
    }

    /**
     *  Вернуть отношение первого параметра к второму
     */
    private function divide(int $firstParam, int $secondParam): int
    {
        return $firstParam / $secondParam;
    }

    /**
     * Управлять файберами
     *
     * @throws \Throwable
     */
    private function manageFibers(array $expected, string $taskName): void
    {
        $this->fiberManager->start($taskName);

        if ($this->fiberManager->isStarted($taskName)) {
            echo '* Запуск' . PHP_EOL;
        }

        $this->fiberManager->throw($taskName, new \Exception($expected[1]));

        $this->fiberManager->execute();
        $this->fiberManager->execute();

        if ($this->fiberManager->isSuspended($taskName) && !$this->fiberManager->isTerminated($taskName)) {
            echo '* Завершение' . PHP_EOL;
        }

        $this->fiberManager->resume($taskName);

        if ($this->fiberManager->getReturns($taskName) !== $expected || $this->fiberManager->getReturn($taskName) !== $expected[2]) {
            $this->fiberManager->removeCompleted();

            echo '* Сравнение полученных значений с ожидаемыми' . PHP_EOL;
        }
    }
}
