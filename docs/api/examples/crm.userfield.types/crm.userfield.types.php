<?php
declare(strict_types=1);

use Bitrix24\SDK\Tests\Integration\Fabric;

require_once 'tests/bootstrap.php';
$serviceBuilder = Fabric::getServiceBuilder(true);

//generated_example_code_start

try {
    $userfieldService = $serviceBuilder->getCRMScope()->userfield();
    $userfieldTypesResult = $userfieldService->types();
    
    foreach ($userfieldTypesResult->getTypes() as $item) {
        print("ID: " . $item->ID . "\n");
        print("Title: " . $item->title . "\n");
    }
} catch (Throwable $e) {
    print("Error: " . $e->getMessage() . "\n");
}

//generated_example_code_finish