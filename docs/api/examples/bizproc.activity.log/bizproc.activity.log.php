<?php
declare(strict_types=1);

use Bitrix24\SDK\Tests\Integration\Fabric;

require_once 'tests/bootstrap.php';
$serviceBuilder = Fabric::getServiceBuilder(true);

//generated_example_code_start

try {
    $eventToken = 'your_event_token'; // Replace with actual event token
    $message = 'Your log message'; // Replace with actual message

    $result = $serviceBuilder
        ->getBizProcScope()
        ->activity()
        ->log($eventToken, $message);

    if ($result->isSuccess()) {
        print($result->getCoreResponse()->getResponseData()->getResult()[0]);
    } else {
        print('Log entry failed.');
    }
} catch (Throwable $e) {
    print('Error: ' . $e->getMessage());
}

//generated_example_code_finish