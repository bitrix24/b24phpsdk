<?php
declare(strict_types=1);

use Bitrix24\SDK\Tests\Integration\Fabric;

require_once 'tests/bootstrap.php';
$serviceBuilder = Fabric::getServiceBuilder(true);

//generated_example_code_start

try {
    $entityTypeId = 1; // Set your entity type ID
    $id = 123; // Set the ID of the item to update
    $fields = [
        'TITLE' => 'Updated Title',
        'DATE_MODIFIED' => (new DateTime())->format(DateTime::ATOM), // Example DateTime field
        // Add other fields as necessary
    ];

    $itemService = $serviceBuilder->getCRMScope()->item();
    $updateResult = $itemService->update($entityTypeId, $id, $fields);

    if ($updateResult->isSuccess()) {
        print("Item updated successfully: " . json_encode($updateResult));
    } else {
        print("Failed to update item.");
    }
} catch (Throwable $e) {
    print("An error occurred: " . $e->getMessage());
}

//generated_example_code_finish