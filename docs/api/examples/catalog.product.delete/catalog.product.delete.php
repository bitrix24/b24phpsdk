<?php
declare(strict_types=1);

use Bitrix24\SDK\Tests\Integration\Fabric;

require_once 'tests/bootstrap.php';
$serviceBuilder = Fabric::getServiceBuilder(true);

//generated_example_code_start

try {
    $productId = 123; // Replace with the actual product ID you want to delete
    $result = $serviceBuilder
        ->getCatalogScope()
        ->product()
        ->delete($productId);

    if ($result->isSuccess()) {
        print("Product with ID {$productId} was deleted successfully.");
    } else {
        print("Failed to delete product with ID {$productId}.");
    }
} catch (Throwable $e) {
    print("An error occurred: " . $e->getMessage());
}

//generated_example_code_finish