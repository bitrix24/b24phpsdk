<?php
declare(strict_types=1);

use Bitrix24\SDK\Tests\Integration\Fabric;

require_once 'tests/bootstrap.php';
$serviceBuilder = Fabric::getServiceBuilder(true);

//generated_example_code_start

try {
    $result = $serviceBuilder
        ->getBizProcScope()
        ->robot()
        ->update(
            'robot_code',
            'https://example.com/handler',
            1,
            ['en' => 'Localized Name'],
            true,
            ['property1' => 'value1'],
            false,
            ['returnProperty1']
        );

    // Process the result
    if ($result->isSuccess()) {
        print_r($result->getCoreResponse()->getResponseData()->getResult());
    } else {
        print("Update failed.");
    }
} catch (Throwable $e) {
    print("An error occurred: " . $e->getMessage());
}

//generated_example_code_finish