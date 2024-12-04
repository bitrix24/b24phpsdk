<?php
declare(strict_types=1);

use Bitrix24\SDK\Tests\Integration\Fabric;

require_once 'tests/bootstrap.php';
$serviceBuilder = Fabric::getServiceBuilder(true);

//generated_example_code_start

try {
    $sipService = $serviceBuilder->getTelephonyScope()->getVoximplantScope()->getSip();
    $result = $sipService->getConnectorStatus();
    $status = $result->getStatus();

    print("Free Minutes: " . $status->FREE_MINUTES . "\n");
    print("Paid: " . ($status->PAID ? 'Yes' : 'No') . "\n");
    print("Paid Date End: " . ($status->PAID_DATE_END ? $status->PAID_DATE_END->format(DATE_ATOM) : 'N/A') . "\n");
} catch (Throwable $e) {
    print("Error: " . $e->getMessage() . "\n");
}

//generated_example_code_finish