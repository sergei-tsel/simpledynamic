<?php

declare(strict_types=1);

use PhpCsFixer\Fixer\ArrayNotation\ArraySyntaxFixer;
use PhpCsFixer\Fixer\FunctionNotation\MethodArgumentSpaceFixer;
use PhpCsFixer\Fixer\Import\NoUnusedImportsFixer;
use PhpCsFixer\Fixer\ListNotation\ListSyntaxFixer;
use PhpCsFixer\Fixer\Whitespace\TypeDeclarationSpacesFixer;
use Symplify\EasyCodingStandard\Config\ECSConfig;

return ECSConfig::configure()
    ->withPaths([
        __DIR__ . '/app',
        __DIR__ . '/config',
        __DIR__ . '/public',
    ])

    ->withPhpCsFixerSets(
        doctrineAnnotation: true,
        php83Migration: true,
        psr1: true,
        psr2: true,
        psr12: true,
        psr12Risky: true,
    )

    ->withConfiguredRule(
        ArraySyntaxFixer::class,
        [],
    )

    // add a single rule
    ->withRules([
        NoUnusedImportsFixer::class,
        ListSyntaxFixer::class,
        MethodArgumentSpaceFixer::class,
    ])

    // add sets - group of rules
    ->withPreparedSets(
         psr12: true,
         //arrays: true,
         //namespaces: true,
         //spaces: true,
         //docblocks: true,
         ///comments: true,
    )
     
     ;
