<?php
declare(strict_types=1);

use Bitrix24\SDK\Tests\Integration\Fabric;

require_once 'tests/bootstrap.php';
$serviceBuilder = Fabric::getServiceBuilder(true);

//generated_example_code_start

try {
    $callId = 'some-call-id'; // Replace with actual call ID
    $b24UserId = [1, 2, 3]; // Replace with actual user IDs

    $result = $serviceBuilder
        ->getTelephonyScope()
        ->externalCall()
        ->hide($callId, $b24UserId);

    if ($result->isSuccess()) {
        print_r($result->getResult());
    } else {
        print("Failed to hide call information.");
    }
} catch (Throwable $e) {
    print("An error occurred: " . $e->getMessage());
}

//generated_example_code_finish