<?php
declare(strict_types=1);

use Bitrix24\SDK\Tests\Integration\Fabric;

require_once 'tests/bootstrap.php';
$serviceBuilder = Fabric::getServiceBuilder(true);

//generated_example_code_start

try {
    $categoryId = 0; // Set your category ID here
    $result = $serviceBuilder
        ->getCRMScope()
        ->dealCategoryStage()
        ->list($categoryId);
    
    foreach ($result->getDealCategoryStages() as $item) {
        print("Name: " . $item->NAME . "\n");
        print("Sort: " . $item->SORT . "\n");
        print("Status ID: " . $item->STATUS_ID . "\n");
    }
} catch (Throwable $e) {
    print("Error: " . $e->getMessage());
}

//generated_example_code_finish