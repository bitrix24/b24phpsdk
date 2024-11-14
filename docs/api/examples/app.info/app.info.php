<?php
declare(strict_types=1);

use Bitrix24\SDK\Tests\Integration\Fabric;

require_once 'tests/bootstrap.php';
$serviceBuilder = Fabric::getServiceBuilder(true);

//generated_example_code_start

try {
    $applicationInfoResult = $serviceBuilder->getMainScope()->main()->getApplicationInfo();
    $itemResult = $applicationInfoResult->applicationInfo();

    print("ID: " . $itemResult->ID . PHP_EOL);
    print("Code: " . $itemResult->CODE . PHP_EOL);
    print("Scope: " . json_encode($itemResult->SCOPE, JSON_THROW_ON_ERROR) . PHP_EOL);
    print("Version: " . $itemResult->VERSION . PHP_EOL);
    print("Status: " . $itemResult->getStatus()->getStatusCode() . PHP_EOL);
    print("Installed: " . ($itemResult->INSTALLED ? 'true' : 'false') . PHP_EOL);
    print("Payment Expired: " . $itemResult->PAYMENT_EXPIRED . PHP_EOL);
    print("Days: " . $itemResult->DAYS . PHP_EOL);
    print("License: " . $itemResult->LICENSE . PHP_EOL);
} catch (Throwable $e) {
    print("Error: " . $e->getMessage() . PHP_EOL);
}

//generated_example_code_finish