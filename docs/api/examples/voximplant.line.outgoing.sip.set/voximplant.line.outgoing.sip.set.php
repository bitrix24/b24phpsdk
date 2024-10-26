<?php
declare(strict_types=1);

use Bitrix24\SDK\Tests\Integration\Fabric;

require_once 'tests/bootstrap.php';
$serviceBuilder = Fabric::getServiceBuilder(true);

//generated_example_code_start

try {
    $sipLineId = 123; // Example SIP Line ID
    $result = $serviceBuilder
        ->getTelephonyScope()
        ->voximplant()
        ->line()
        ->outgoingSipSet($sipLineId);

    if ($result->isSuccess()) {
        print($result->getCoreResponse()->getResponseData()->getResult()[0]);
    } else {
        print("Error: Unable to set outgoing SIP line.");
    }
} catch (Throwable $e) {
    print("Exception: " . $e->getMessage());
}

//generated_example_code_finish