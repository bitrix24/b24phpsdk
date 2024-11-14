<?php
declare(strict_types=1);

use PhpCsFixer\Config;
use PhpCsFixer\Finder;

$finder = Finder::create()
    ->in(__DIR__ . '/src/Infrastructure/Console/Commands/')
    ->name('*.php')
    ->exclude(['vendor', 'storage', 'docker', 'docs']) // Exclude directories
    ->ignoreDotFiles(true)
    ->ignoreVCS(true);

return (new Config())
    ->setCacheFile(__DIR__ . '/.php-cs-fixer.cache') // Cache file location
    ->setParallelConfig(PhpCsFixer\Runner\Parallel\ParallelConfigFactory::detect())
    ->setFinder($finder)
    ->setRules([
        '@PSR12' => true, // PSR-12 coding standards
    ]);