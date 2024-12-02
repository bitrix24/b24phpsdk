<?php
declare(strict_types=1);

use Bitrix24\SDK\Tests\Integration\Fabric;

require_once 'tests/bootstrap.php';
$serviceBuilder = Fabric::getServiceBuilder(true);

//generated_example_code_start

try {
    $itemId = 123; // Replace with the actual item ID to delete
    $result = $serviceBuilder->getCRMScope()->activity()->delete($itemId);
    
    if ($result->isSuccess()) {
        print("Item deleted successfully.");
    } else {
        print("Failed to delete item.");
    }
} catch (Throwable $e) {
    print("Error occurred: " . $e->getMessage());
}

//generated_example_code_finish