<?php
declare(strict_types=1);

use Bitrix24\SDK\Tests\Integration\Fabric;

require_once 'tests/bootstrap.php';
$serviceBuilder = Fabric::getServiceBuilder(true);

//generated_example_code_start

try {
    $lineId = 'your_line_id'; // replace with actual line ID
    $toNumber = 'your_to_number'; // replace with actual destination number
    $recordUrl = 'your_record_url'; // replace with actual record URL

    $result = $serviceBuilder
        ->getTelephonyScope()
        ->voximplant()
        ->infoCall()
        ->startWithSound($lineId, $toNumber, $recordUrl);

    $itemResult = $result->getCallResult();
    print($itemResult->CALL_ID);
    print($itemResult->RESULT);
} catch (Throwable $e) {
    // Handle exception
    print('Error: ' . $e->getMessage());
}

//generated_example_code_finish