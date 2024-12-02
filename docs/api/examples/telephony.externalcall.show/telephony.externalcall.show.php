<?php
declare(strict_types=1);

use Bitrix24\SDK\Tests\Integration\Fabric;

require_once 'tests/bootstrap.php';
$serviceBuilder = Fabric::getServiceBuilder(true);

//generated_example_code_start

try {
    $callId = 'your_call_id'; // replace with your actual call ID
    $b24UserId = [1, 2, 3]; // replace with actual user IDs

    $result = $serviceBuilder
        ->getTelephonyScope()
        ->externalCall()
        ->show($callId, $b24UserId);

    if ($result->isSuccess()) {
        $itemResult = $result->getCoreResponse()->getResponseData()->getResult()[0];
        print($itemResult); // Assuming ItemResult is a public property
    } else {
        print('Failed to show call: ' . json_encode($result->getCoreResponse()->getResponseData()->getError()));
    }
} catch (Throwable $e) {
    print('Error: ' . $e->getMessage());
}

//generated_example_code_finish