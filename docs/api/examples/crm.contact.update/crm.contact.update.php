<?php
declare(strict_types=1);

use Bitrix24\SDK\Tests\Integration\Fabric;

require_once 'tests/bootstrap.php';
$serviceBuilder = Fabric::getServiceBuilder(true);

//generated_example_code_start

try {
    $contactId = 123; // Example contact ID
    $fields = [
        'NAME' => 'John',
        'LAST_NAME' => 'Doe',
        'BIRTHDATE' => (new DateTime('1990-01-01'))->format(DateTime::ATOM),
        'PHONE' => '123456789',
        'EMAIL' => 'john.doe@example.com',
        'ADDRESS' => '123 Main St',
        'ADDRESS_CITY' => 'Anytown',
        'ADDRESS_COUNTRY' => 'USA',
    ];
    $params = [
        'REGISTER_SONET_EVENT' => 'Y',
    ];

    $result = $serviceBuilder
        ->getCRMScope()
        ->contact()
        ->update($contactId, $fields, $params);

    if ($result->isSuccess()) {
        print($result->getCoreResponse()->getResponseData()->getResult()[0]);
    } else {
        print('Update failed.');
    }
} catch (Throwable $e) {
    print('Error: ' . $e->getMessage());
}

//generated_example_code_finish