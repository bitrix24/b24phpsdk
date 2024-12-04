<?php
declare(strict_types=1);

use Bitrix24\SDK\Tests\Integration\Fabric;

require_once 'tests/bootstrap.php';
$serviceBuilder = Fabric::getServiceBuilder(true);

//generated_example_code_start

try {
    $id = 123; // Example lead ID
    $fields = [
        'TITLE' => 'Updated Lead Title',
        'NAME' => 'John',
        'LAST_NAME' => 'Doe',
        'BIRTHDATE' => (new DateTime('1980-01-01'))->format(DateTime::ATOM),
        'COMPANY_TITLE' => 'Example Company',
        'STATUS_ID' => 'NEW',
        'COMMENTS' => 'Updated comments for the lead.',
        'PHONE' => '1234567890',
        'EMAIL' => 'john.doe@example.com',
    ];
    $params = [
        'REGISTER_SONET_EVENT' => 'Y',
    ];

    $result = $serviceBuilder->getCRMScope()->lead()->update($id, $fields, $params);

    if ($result->isSuccess()) {
        print($result->getCoreResponse()->getResponseData()->getResult()[0]);
    } else {
        print("Update failed.");
    }
} catch (Throwable $e) {
    print("Error: " . $e->getMessage());
}

//generated_example_code_finish