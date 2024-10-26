<?php
declare(strict_types=1);

use Bitrix24\SDK\Tests\Integration\Fabric;

require_once 'tests/bootstrap.php';
$serviceBuilder = Fabric::getServiceBuilder(true);

//generated_example_code_start

try {
    $result = $serviceBuilder
        ->getCRMScope()
        ->dealCategory()
        ->getDefaultCategorySettings();
    
    $itemResult = $result->getDealCategoryFields();
    
    print("ID: " . $itemResult->ID . "\n");
    print("Created Date: " . $itemResult->CREATED_DATE->format(DATE_ATOM) . "\n");
    print("Name: " . $itemResult->NAME . "\n");
    print("Is Locked: " . ($itemResult->IS_LOCKED ? 'true' : 'false') . "\n");
    print("Sort: " . $itemResult->SORT . "\n");
} catch (Throwable $e) {
    print("Error: " . $e->getMessage() . "\n");
}

//generated_example_code_finish