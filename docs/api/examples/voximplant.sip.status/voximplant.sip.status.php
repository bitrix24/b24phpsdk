<?php
declare(strict_types=1);

use Bitrix24\SDK\Tests\Integration\Fabric;

require_once 'tests/bootstrap.php';
$serviceBuilder = Fabric::getServiceBuilder(true);

//generated_example_code_start

try {
    $sipRegistrationId = 123; // Example SIP registration ID
    $result = $serviceBuilder
        ->getTelephonyScope()
        ->getVoximplantScope()
        ->getSipScope()
        ->status($sipRegistrationId);
    
    $itemResult = $result->getStatus();
    
    print("REG_ID: " . $itemResult->REG_ID . "\n");
    print("LAST_UPDATED: " . $itemResult->LAST_UPDATED->format(DATE_ATOM) . "\n");
    print("ERROR_MESSAGE: " . ($itemResult->ERROR_MESSAGE ?? 'None') . "\n");
    print("STATUS_CODE: " . ($itemResult->STATUS_CODE ?? 'None') . "\n");
    print("STATUS_RESULT: " . $itemResult->STATUS_RESULT->value . "\n");
} catch (Throwable $e) {
    print("Error: " . $e->getMessage() . "\n");
}

//generated_example_code_finish