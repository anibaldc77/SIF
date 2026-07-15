<?php
declare(strict_types=1);

use PhpCsFixer\Config;
use PhpCsFixer\Finder;

$finder = Finder::create()
    ->in(__DIR__.'/src')
    ->in(__DIR__.'/tests')
    ->exclude('Foundation')
    ->name('*.php');

return (new Config())
    ->setRiskyAllowed(false)
    ->setCacheFile(__DIR__.'/var/php-cs-fixer/.php-cs-fixer.cache')
    ->setRules([
        '@PSR12' => true,
        'native_function_invocation' => false,
        'single_line_empty_body' => false,
    ])
    ->setFinder($finder);
