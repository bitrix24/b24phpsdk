<?php
declare(strict_types=1);

use Bitrix24\SDK\Tests\Integration\Fabric;

require_once 'tests/bootstrap.php';
$serviceBuilder = Fabric::getServiceBuilder(true);

//generated_example_code_start

try {
    $entityTypeId = 1; // Replace with actual entity type ID
    $order = []; // Replace with actual order array
    $filter = []; // Replace with actual filter array
    $select = []; // Replace with actual select array
    $startItem = 0; // Optional, can be adjusted as needed

    $itemsResult = $serviceBuilder
        ->getCRMScope()
        ->item()
        ->list($entityTypeId, $order, $filter, $select, $startItem);

    foreach ($itemsResult->getItems() as $item) {
        print("ID: " . $item->id . PHP_EOL);
        print("XML ID: " . $item->xmlId . PHP_EOL);
        print("Title: " . $item->title . PHP_EOL);
        print("Created By: " . $item->createdBy . PHP_EOL);
        print("Updated By: " . $item->updatedBy . PHP_EOL);
        print("Created Time: " . $item->createdTime->format(DATE_ATOM) . PHP_EOL);
        print("Updated Time: " . $item->updatedTime->format(DATE_ATOM) . PHP_EOL);
        // Add more fields as necessary
    }
} catch (Throwable $e) {
    print("Error: " . $e->getMessage() . PHP_EOL);
}

//generated_example_code_finish