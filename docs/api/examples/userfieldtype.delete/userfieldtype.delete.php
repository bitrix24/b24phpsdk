<?php
declare(strict_types=1);

use Bitrix24\SDK\Tests\Integration\Fabric;

require_once 'tests/bootstrap.php';
$serviceBuilder = Fabric::getServiceBuilder(true);

//generated_example_code_start

try {
    $userTypeId = 'example_user_type_id'; // Replace with the actual user type ID
    $result = $serviceBuilder
        ->getPlacementScope()
        ->userFieldType()
        ->delete($userTypeId);

    if ($result->isSuccess()) {
        print($result->getCoreResponse()->getResponseData()->getResult()[0]);
    } else {
        print("Error occurred while deleting user field type.");
    }
} catch (\Throwable $e) {
    print("Exception: " . $e->getMessage());
}

//generated_example_code_finish