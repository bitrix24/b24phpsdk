<?php
declare(strict_types=1);

use Bitrix24\SDK\Tests\Integration\Fabric;

require_once 'tests/bootstrap.php';
$serviceBuilder = Fabric::getServiceBuilder(true);

//generated_example_code_start

try {
    $fields = [
        'TITLE' => 'New Lead Title',
        'NAME' => 'John',
        'LAST_NAME' => 'Doe',
        'BIRTHDATE' => (new DateTime('1980-01-01'))->format(DateTime::ATOM),
        'COMPANY_TITLE' => 'Example Company',
        'SOURCE_ID' => 'WEB',
        'STATUS_ID' => 'NEW',
        'PHONE' => '+1234567890',
        'EMAIL' => 'john.doe@example.com',
        'ADDRESS' => '123 Main St',
        'ADDRESS_CITY' => 'Anytown',
        'ADDRESS_COUNTRY' => 'USA',
        'CURRENCY_ID' => 'USD',
        'OPPORTUNITY' => '1000',
        'ASSIGNED_BY_ID' => 1,
    ];

    $params = [
        'REGISTER_SONET_EVENT' => 'Y',
    ];

    $result = $serviceBuilder->getCRMScope()
        ->lead()
        ->add($fields, $params);

    print($result->getId());
} catch (Throwable $e) {
    print('Error: ' . $e->getMessage());
}

//generated_example_code_finish