<?php
declare(strict_types=1);

use Bitrix24\SDK\Tests\Integration\Fabric;

require_once 'tests/bootstrap.php';
$serviceBuilder = Fabric::getServiceBuilder(true);

//generated_example_code_start

try {
    $lineId = 'your_line_id_here'; // Replace with the actual line ID
    $result = $serviceBuilder
        ->getTelephonyScope()
        ->voximplant()
        ->line()
        ->outgoingSet($lineId);

    if ($result->isSuccess()) {
        print($result->getCoreResponse()->getResponseData()->getResult()[0]);
    } else {
        print('Failed to set outgoing line.');
    }
} catch (Throwable $e) {
    print('Error: ' . $e->getMessage());
}

//generated_example_code_finish