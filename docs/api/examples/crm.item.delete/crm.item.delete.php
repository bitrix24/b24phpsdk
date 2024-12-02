<?php
declare(strict_types=1);

use Bitrix24\SDK\Tests\Integration\Fabric;

require_once 'tests/bootstrap.php';
$serviceBuilder = Fabric::getServiceBuilder(true);

//generated_example_code_start

try {
    $entityTypeId = 1; // Example entity type ID
    $id = 123; // Example item ID to delete

    $result = $serviceBuilder
        ->getCRMScope()
        ->item()
        ->delete($entityTypeId, $id);

    if ($result->isSuccess()) {
        print("Item with ID $id deleted successfully.");
    } else {
        print("Failed to delete item: " . json_encode($result->getCoreResponse()->getResponseData()->getError()));
    }
} catch (Throwable $e) {
    print("An error occurred: " . $e->getMessage());
}

//generated_example_code_finish