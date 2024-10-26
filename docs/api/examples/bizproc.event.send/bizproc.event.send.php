<?php
declare(strict_types=1);

use Bitrix24\SDK\Tests\Integration\Fabric;

require_once 'tests/bootstrap.php';
$serviceBuilder = Fabric::getServiceBuilder(true);

//generated_example_code_start

try {
    $eventToken = 'your_event_token'; // replace with actual event token
    $returnValues = [
        'key1' => 'value1',
        'key2' => 'value2',
        // add more key-value pairs as needed
    ];
    $logMessage = 'Your log message'; // optional log message

    $result = $serviceBuilder
        ->getBizProcScope()
        ->event()
        ->send($eventToken, $returnValues, $logMessage);

    if ($result->isSuccess()) {
        print_r($result->getCoreResponse()->getResponseData()->getResult());
    } else {
        print("Error sending event: " . $result->getErrorMessages());
    }
} catch (Throwable $e) {
    print("An error occurred: " . $e->getMessage());
}

//generated_example_code_finish