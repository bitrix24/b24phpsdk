<?php
declare(strict_types=1);

use Bitrix24\SDK\Tests\Integration\Fabric;

require_once 'tests/bootstrap.php';
$serviceBuilder = Fabric::getServiceBuilder(true);

//generated_example_code_start

try {
    $result = $serviceBuilder
        ->getPlacementScope()
        ->userFieldType()
        ->add('custom_user_type', 'https://example.com/handler', 'Custom User Type', 'This is a custom user field type.');

    if ($result->isSuccess()) {
        print($result->getCoreResponse()->getResponseData()->getResult()[0]);
    } else {
        print("Error: " . $result->getCoreResponse()->getErrorMessage());
    }
} catch (Throwable $e) {
    print("Exception: " . $e->getMessage());
}

//generated_example_code_finish