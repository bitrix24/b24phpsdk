<?php
declare(strict_types=1);

use Bitrix24\SDK\Tests\Integration\Fabric;

require_once 'tests/bootstrap.php';
$serviceBuilder = Fabric::getServiceBuilder(true);

//generated_example_code_start

try {
    $userId = 123; // Replace with the actual user ID
    $result = $serviceBuilder
        ->getTelephonyScope()
        ->getVoximplantUserService()
        ->deactivatePhone($userId);

    if ($result->isSuccess()) {
        print($result->getCoreResponse()->getResponseData()->getResult()[0]);
    } else {
        print('Failed to deactivate phone.');
    }
} catch (Throwable $e) {
    print('Error: ' . $e->getMessage());
}

//generated_example_code_finish