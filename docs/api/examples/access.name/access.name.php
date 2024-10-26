<?php
declare(strict_types=1);

use Bitrix24\SDK\Tests\Integration\Fabric;

require_once 'tests/bootstrap.php';
$serviceBuilder = Fabric::getServiceBuilder(true);

//generated_example_code_start

try {
    $accessList = ['permission1', 'permission2']; // Example access list
    $response = $serviceBuilder->getMainScope()->getAccessName($accessList);
    $result = $response->getResponseData();

    // Assuming ItemResult is a property of ResponseData
    foreach ($result->getItems() as $item) {
        print($item->accessName); // Assuming accessName is a public property of the item
    }
} catch (Throwable $exception) {
    // Handle the exception
    print("Error: " . $exception->getMessage());
}

//generated_example_code_finish