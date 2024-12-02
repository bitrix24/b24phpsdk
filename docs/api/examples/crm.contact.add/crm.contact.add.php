<?php
declare(strict_types=1);

use Bitrix24\SDK\Tests\Integration\Fabric;

require_once 'tests/bootstrap.php';
$serviceBuilder = Fabric::getServiceBuilder(true);

//generated_example_code_start

try {
    $fields = [
        'NAME' => 'John',
        'LAST_NAME' => 'Doe',
        'BIRTHDATE' => (new DateTime('1990-01-01'))->format(DateTime::ATOM),
        'PHONE' => '+1234567890',
        'EMAIL' => 'john.doe@example.com',
        'ADDRESS' => '123 Main St',
        'ADDRESS_CITY' => 'Anytown',
        'ADDRESS_COUNTRY' => 'USA',
        'ASSIGNED_BY_ID' => '1',
        'COMPANY_ID' => '2',
    ];

    $params = [
        'REGISTER_SONET_EVENT' => 'N',
    ];

    $result = $serviceBuilder->getCRMScope()
        ->contact()
        ->add($fields, $params);

    print($result->getId());
} catch (Throwable $e) {
    print('Error: ' . $e->getMessage());
}

//generated_example_code_finish