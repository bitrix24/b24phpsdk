<?php
declare(strict_types=1);

use Bitrix24\SDK\Tests\Integration\Fabric;

require_once 'tests/bootstrap.php';
$serviceBuilder = Fabric::getServiceBuilder(true);

//generated_example_code_start

try {
    $placementCode = 'your_placement_code'; // Replace with your actual placement code
    $handlerUrl = null; // Optional handler URL

    $result = $serviceBuilder
        ->getPlacementScope()
        ->unbind($placementCode, $handlerUrl);

    // Process the return result
    $deletedCount = $result->getDeletedPlacementHandlersCount();
    print("Deleted Placement Handlers Count: " . $deletedCount);
} catch (Throwable $e) {
    print("Error: " . $e->getMessage());
}

//generated_example_code_finish