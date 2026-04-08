<?php

declare(strict_types=1);

use PhpCsFixer\Fixer\ArrayNotation\ArraySyntaxFixer;
use PhpCsFixer\Fixer\FunctionNotation\MethodArgumentSpaceFixer;
use PhpCsFixer\Fixer\Import\NoUnusedImportsFixer;
use PhpCsFixer\Fixer\ListNotation\ListSyntaxFixer;
use Symplify\EasyCodingStandard\Config\ECSConfig;

return ECSConfig::configure()
    ->withPaths([
        __DIR__ . '/config',
        __DIR__ . '/framework',
        __DIR__ . '/public',
    ])->withPhpCsFixerSets(
        doctrineAnnotation: true,
        php83Migration: true,
        php84Migration: true,
        psr1: true,
        psr2: true,
        psr12: true,
        psr12Risky: true,
        php85Migration: true,
    )

    ->withConfiguredRule(
        ArraySyntaxFixer::class,
        [],
    )->withRules([ // add a single rule
        NoUnusedImportsFixer::class,
        ListSyntaxFixer::class,
        MethodArgumentSpaceFixer::class,
    ])->withPreparedSets( // add sets - group of rules
        psr12: true,
        //arrays: true,
        //namespaces: true,
        //spaces: true,
        //docblocks: true,
        ///comments: true,
    );
