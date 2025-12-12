<?php

/**
 * This file is part of the B24PhpSdk package.
 *
 * For the full copyright and license information, please view the MIT-LICENSE.txt
 * file that was distributed with this source code.
 */

declare(strict_types=1);

use Rector\Config\RectorConfig;
use Rector\Naming\Rector\Class_\RenamePropertyToMatchTypeRector;
use Rector\PHPUnit\Set\PHPUnitSetList;
use Rector\Set\ValueObject\LevelSetList;

return RectorConfig::configure()
    ->withPaths([
        __DIR__ . '/src/Core/',
        __DIR__ . '/src/Application/',
        __DIR__ . '/src/Services/Telephony',
        __DIR__ . '/tests/Integration/Services/Telephony',
        __DIR__ . '/src/Services/Catalog',
        __DIR__ . '/tests/Integration/Services/Catalog',
        __DIR__ . '/src/Services/User',
        __DIR__ . '/tests/Integration/Services/User',
        __DIR__ . '/src/Services/UserConsent',
        __DIR__ . '/tests/Integration/Services/UserConsent',
        __DIR__ . '/src/Services/IM',
        __DIR__ . '/tests/Integration/Services/IM',
        __DIR__ . '/src/Services/IMOpenLines',
        __DIR__ . '/tests/Integration/Services/IMOpenLines',
        __DIR__ . '/src/Services/SonetGroup',
        __DIR__ . '/tests/Integration/Services/SonetGroup',
        __DIR__ . '/src/Services/CRM/Address',
        __DIR__ . '/tests/Integration/Services/CRM/Address',
        __DIR__ . '/src/Services/Main',
        __DIR__ . '/tests/Integration/Services/Main',
        __DIR__ . '/src/Services/Paysystem',
        __DIR__ . '/tests/Integration/Services/Paysystem',
        __DIR__ . '/src/Services/Placement',
        __DIR__ . '/tests/Integration/Services/Placement',
        __DIR__ . '/src/Services/CRM/Deal',
        __DIR__ . '/tests/Integration/Services/CRM/Deal/Service',
        __DIR__ . '/src/Services/CRM/Item',
        __DIR__ . '/tests/Integration/Services/CRM/Item',
        __DIR__ . '/src/Services/CRM/Deal/Service/DealDetailsConfiguration.php',
        __DIR__ . '/tests/Integration/Services/CRM/Deal/Service/DealDetailsConfigurationTest.php',
        __DIR__ . '/src/Services/CRM/Contact/Service/ContactDetailsConfiguration.php',
        __DIR__ . '/tests/Integration/Services/CRM/Contact/Service/ContactDetailsConfigurationTest.php',
        __DIR__ . '/src/Services/CRM/Lead',
        __DIR__ . '/tests/Integration/Services/CRM/Lead/Service',
        __DIR__ . '/src/Services/CRM/Quote',
        __DIR__ . '/tests/Integration/Services/CRM/Quote/Service',
        __DIR__ . '/src/Services/CRM/Currency',
        __DIR__ . '/tests/Integration/Services/CRM/Currency',
        __DIR__ . '/src/Services/CRM/Requisites',
        __DIR__ . '/tests/Integration/Services/CRM/Requisites',
        __DIR__ . '/src/Services/CRM/Timeline',
        __DIR__ . '/tests/Integration/Services/CRM/Timeline',
        __DIR__ . '/src/Services/Entity/Section',
        __DIR__ . '/tests/Integration/Services/Entity/Section',
        __DIR__ . '/src/Services/Department',
        __DIR__ . '/tests/Integration/Services/Department',
        __DIR__ . '/src/Services/Task',
        __DIR__ . '/tests/Integration/Services/Task',
        __DIR__ . '/src/Services/Sale',
        __DIR__ . '/tests/Integration/Services/Sale',
        __DIR__ . '/src/Services/Disk',
        __DIR__ . '/tests/Integration/Services/Disk',
        __DIR__ . '/src/Services/Calendar',
        __DIR__ . '/tests/Integration/Services/Calendar',
        __DIR__ . '/src/Services/CRM/Documentgenerator/Numerator',
        __DIR__ . '/tests/Integration/Services/CRM/Documentgenerator/Numerator',
        __DIR__ . '/tests/Unit/',
    ])
    ->withCache(cacheDirectory: __DIR__ . '/var/.cache/rector')
    ->withSets(
        [
            LevelSetList::UP_TO_PHP_84,
            PHPUnitSetList::PHPUNIT_110
        ]
    )
    ->withImportNames(
        importNames: false,
        importDocBlockNames: false,
        importShortClasses: false,
        removeUnusedImports: false,
    )
    ->withPhpSets(
        php84: true   // 8.4
    )
    ->withPreparedSets(
        deadCode: true,
        codeQuality: true,
        codingStyle: true,
        typeDeclarations: true,
        privatization: true,
        naming: true,
        instanceOf: true,
        earlyReturn: true,
        strictBooleans: true
    )
    ->withSkip([
        RenamePropertyToMatchTypeRector::class
    ]);