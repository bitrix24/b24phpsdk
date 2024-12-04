<?php
declare(strict_types=1);

use Bitrix24\SDK\Tests\Integration\Fabric;

require_once 'tests/bootstrap.php';
$serviceBuilder = Fabric::getServiceBuilder(true);

//generated_example_code_start

try {
    $categoryId = 1; // Replace with your actual category ID
    $result = $serviceBuilder
        ->getCRMScope()
        ->dealCategory()
        ->getStatus($categoryId);

    // Process the result
    print($result->getDealCategoryTypeId());
} catch (Throwable $e) {
    // Handle the exception
    print("Error: " . $e->getMessage());
}

//generated_example_code_finish