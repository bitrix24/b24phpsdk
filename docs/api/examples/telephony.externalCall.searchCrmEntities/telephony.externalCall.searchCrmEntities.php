<?php
declare(strict_types=1);

use Bitrix24\SDK\Tests\Integration\Fabric;

require_once 'tests/bootstrap.php';
$serviceBuilder = Fabric::getServiceBuilder(true);

//generated_example_code_start

try {
    $phoneNumber = '1234567890'; // Replace with a valid phone number
    $result = $serviceBuilder
        ->getTelephonyScope()
        ->externalCall()
        ->searchCrmEntities($phoneNumber);

    foreach ($result->getCrmEntities() as $item) {
        print("CRM Entity Type: " . $item->CRM_ENTITY_TYPE . "\n");
        print("CRM Entity ID: " . $item->CRM_ENTITY_ID . "\n");
        print("Assigned By ID: " . $item->ASSIGNED_BY_ID . "\n");
        print("Name: " . $item->NAME . "\n");
        print("Assigned By: " . $item->ASSIGNED_BY->NAME . "\n"); // Assuming ASSIGNED_BY has a NAME property
    }
} catch (Throwable $e) {
    print("Error: " . $e->getMessage());
}

//generated_example_code_finish