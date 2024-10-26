<?php
declare(strict_types=1);

use Bitrix24\SDK\Tests\Integration\Fabric;

require_once 'tests/bootstrap.php';
$serviceBuilder = Fabric::getServiceBuilder(true);

//generated_example_code_start

try {
    $payload = [
        // Populate the payload with necessary data
        'key1' => 'value1',
        'key2' => 'value2',
        'timestamp' => (new DateTime())->format(DateTime::ATOM),
    ];

    $response = $serviceBuilder
        ->getMainScope()
        ->event()
        ->test($payload);

    $result = $response->getResponseData();

    // Assuming ItemResult is a public property of ResponseData
    foreach ($result->getItems() as $item) {
        print($item->property1);
        print($item->property2);
        // Add more properties as needed
    }
} catch (Throwable $exception) {
    // Handle the exception
    print('Error: ' . $exception->getMessage());
}

//generated_example_code_finish