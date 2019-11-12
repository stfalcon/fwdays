<?php

$finder = PhpCsFixer\Finder::create()
    ->exclude('Migrations')
    ->in(__DIR__.'/src/')
;

return PhpCsFixer\Config::create()
    ->setRules([
        '@Symfony' => true,
        '@Symfony:risky' => true,
        'native_function_invocation' => ['include' => ['@compiler_optimized'], 'scope' => 'namespaced'],
        'no_unused_imports' => true,
        'ordered_imports' => true,
        'self_accessor' => false,
        'no_superfluous_phpdoc_tags' => false,
        'no_empty_phpdoc' => false,
        'array_syntax' => ['syntax' => 'short'],
        'phpdoc_types_order' => ['null_adjustment' => 'always_last', 'sort_algorithm' => 'none'],
    ])
    ->setUsingCache(true)
    ->setRiskyAllowed(true)
    ->setFinder($finder)
;
