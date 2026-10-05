<?php

declare(strict_types=1);

namespace Test\App\Console\Greeting;

use Simpledynamic\Services\CLI\Command;
use Simpledynamic\Services\CLI\Option;
use Simpledynamic\Services\CLI\OptionType;

/**
 * Тестовая команда приветствия
 * greet {--text}
 */
#[Option(type: OptionType::REQUIRED, short: '-t', long: '--text', description: 'Текст приветствия')]
class Greet extends Command
{
    #[\Override]
    protected static string $description = 'Печатает приветствие из переданного текста';

    /**
     * Обработать команду
     *
     * Вывод завершается переводом строки, как и в остальных консольных командах
     * фреймворка, иначе приглашение оболочки приклеивается к приветствию.
     */
    public function handle(): void
    {
        $text = $this->getArgument('text');

        if (!is_string($text) || $text === '') {
            echo 'приветствие' . PHP_EOL;

            return;
        }

        echo 'приветствие: ' . $text . PHP_EOL;
    }
}
