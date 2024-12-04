<?php
declare(strict_types=1);

use Bitrix24\SDK\Tests\Integration\Fabric;

require_once 'tests/bootstrap.php';
$serviceBuilder = Fabric::getServiceBuilder(true);

//generated_example_code_start

try {
    $userId = 123; // Example user ID
    $fields = [
        'NAME' => 'John',
        'LAST_NAME' => 'Doe',
        'EMAIL' => 'john.doe@example.com',
        'PERSONAL_BIRTHDAY' => (new DateTime('1990-01-01'))->format(DateTime::ATOM),
        'WORK_POSITION' => 'Developer',
        // Add other necessary fields here
    ];

    $result = $serviceBuilder
        ->getUserScope()
        ->update($userId, $fields);

    if ($result->isSuccess()) {
        print_r($result->getCoreResponse()->getResponseData()->getResult());
    } else {
        print('Update failed.');
    }
} catch (Throwable $e) {
    print('Error: ' . $e->getMessage());
}

//generated_example_code_finish