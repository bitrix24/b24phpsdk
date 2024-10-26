<?php
declare(strict_types=1);

use Bitrix24\SDK\Tests\Integration\Fabric;

require_once 'tests/bootstrap.php';
$serviceBuilder = Fabric::getServiceBuilder(true);

//generated_example_code_start

try {
    $userFieldTypesResult = $serviceBuilder->getPlacementScope()->userFieldType()->list();
    $userFieldTypes = $userFieldTypesResult->getUserFieldTypes();

    foreach ($userFieldTypes as $userFieldType) {
        print("Description: " . $userFieldType->DESCRIPTION . "\n");
        print("Handler: " . $userFieldType->HANDLER . "\n");
        print("Title: " . $userFieldType->TITLE . "\n");
        print("User Type ID: " . $userFieldType->USER_TYPE_ID . "\n");
    }
} catch (Throwable $e) {
    print("Error: " . $e->getMessage());
}

//generated_example_code_finish