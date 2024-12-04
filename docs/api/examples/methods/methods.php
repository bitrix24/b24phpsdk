<?php
declare(strict_types=1);

use Bitrix24\SDK\Tests\Integration\Fabric;

require_once 'tests/bootstrap.php';
$serviceBuilder = Fabric::getServiceBuilder(true);

//generated_example_code_start

try {
    $scope = 'your_scope_here'; // Replace with the actual scope
    $response = $serviceBuilder
        ->getMainScope()
        ->getMethodsByScope($scope)
        ->getResponseData();
    
    foreach ($response->getItems() as $item) {
        print($item->getMethodName()); // Assuming method name is a public property
        print($item->getDescription()); // Assuming description is a public property
        // Add additional prints for other public properties as needed
    }
} catch (Throwable $e) {
    print('Error: ' . $e->getMessage());
}

//generated_example_code_finish