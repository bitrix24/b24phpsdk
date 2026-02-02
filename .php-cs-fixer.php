<?php
declare(strict_types=1);

use PhpCsFixer\Config;
use PhpCsFixer\Finder;

$finder = Finder::create()
    ->in(__DIR__ . '/src/Infrastructure/Console/Commands/')
    ->in(__DIR__ . '/src/Services/CRM/Address/')
    ->in(__DIR__ . '/src/Services/CRM/Item/')
    ->in(__DIR__ . '/src/Services/CRM/Contact/')
    ->in(__DIR__ . '/src/Services/CRM/Quote/')
    ->in(__DIR__ . '/src/Services/CRM/Lead/')
    ->in(__DIR__ . '/src/Services/CRM/Currency/')
    ->in(__DIR__ . '/src/Services/CRM/Requisites/')
    ->in(__DIR__ . '/src/Services/CRM/Status/')
    ->in(__DIR__ . '/src/Services/CRM/Timeline/')
    ->in(__DIR__ . '/src/Services/CRM/Documentgenerator/Numerator/')
    ->in(__DIR__ . '/src/Services/Entity/Section/')
    ->in(__DIR__ . '/src/Services/Department/')
    ->in(__DIR__ . '/src/Services/Landing/')
    ->in(__DIR__ . '/src/Services/Paysystem/')
    ->in(__DIR__ . '/src/Services/Sale/')
    ->in(__DIR__ . '/src/Services/Task/')
    ->in(__DIR__ . '/src/Services/Sale/')
    ->in(__DIR__ . '/src/Services/Disk/')
    ->in(__DIR__ . '/src/Services/Calendar/')
    ->in(__DIR__ . '/src/Services/Lists/')
    ->in(__DIR__ . '/src/Services/SonetGroup/')
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