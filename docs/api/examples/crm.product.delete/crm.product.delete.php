<?php
declare(strict_types=1);

use Bitrix24\SDK\Tests\Integration\Fabric;

require_once 'tests/bootstrap.php';
$serviceBuilder = Fabric::getServiceBuilder(true);

//generated_example_code_start

try {
    $productId = 123; // Example product ID
    $result = $serviceBuilder->getCRMScope()->product()->delete($productId);
    
    if ($result->isSuccess()) {
        print("Item deleted successfully.");
    } else {
        print("Failed to delete item.");
    }
} catch (Throwable $e) {
    print("An error occurred: " . $e->getMessage());
}

//generated_example_code_finish