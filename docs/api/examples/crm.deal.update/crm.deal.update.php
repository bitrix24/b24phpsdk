<?php
declare(strict_types=1);

use Bitrix24\SDK\Tests\Integration\Fabric;

require_once 'tests/bootstrap.php';
$serviceBuilder = Fabric::getServiceBuilder(true);

//generated_example_code_start

try {
    $id = 123; // Example ID
    $fields = [
        'ID' => $id,
        'TITLE' => 'Updated Deal Title',
        'TYPE_ID' => 'TYPE_1',
        'CATEGORY_ID' => 'CATEGORY_1',
        'STAGE_ID' => 'STAGE_1',
        'CURRENCY_ID' => 'USD',
        'OPPORTUNITY' => '1000',
        'BEGINDATE' => (new DateTime())->format(DateTime::ATOM),
        'CLOSEDATE' => (new DateTime('+1 month'))->format(DateTime::ATOM),
        'COMMENTS' => 'Updated comments',
        'LOCATION_ID' => 'LOCATION_1',
    ];
    $params = [
        'REGISTER_SONET_EVENT' => 'Y',
    ];

    $result = $serviceBuilder
        ->getCRMScope()
        ->deal()
        ->update($id, $fields, $params);

    if ($result->isSuccess()) {
        print_r($result->getCoreResponse()->getResponseData()->getResult());
    } else {
        print_r($result->getCoreResponse()->getError());
    }
} catch (Throwable $e) {
    print('Error: ' . $e->getMessage());
}

//generated_example_code_finish