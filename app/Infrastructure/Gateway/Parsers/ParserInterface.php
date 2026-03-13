<?php

declare(strict_types=1);

namespace App\Infrastructure\Gateway\Parsers;

interface ParserInterface
{
    public function serialize(string $data);

    public function embed(string $resourceData, array $data);
}
