<?php
declare(strict_types=1);

use Bitrix24\SDK\Tests\Integration\Fabric;

require_once 'tests/bootstrap.php';
$serviceBuilder = Fabric::getServiceBuilder(true);

//generated_example_code_start

try {
    $categoryId = 1; // Example category ID
    $fields = [
        'ID' => 1,
        'CREATED_DATE' => (new DateTime())->format(DateTime::ATOM),
        'NAME' => 'Updated Category Name',
        'IS_LOCKED' => 'N',
        'SORT' => 100,
    ];

    $result = $serviceBuilder->getCRMScope()->dealCategory()->update($categoryId, $fields);

    if ($result->isSuccess()) {
        print($result->getCoreResponse()->getResponseData()->getResult()[0]);
    } else {
        print('Update failed.');
    }
} catch (Throwable $e) {
    print('Error: ' . $e->getMessage());
}

//generated_example_code_finish