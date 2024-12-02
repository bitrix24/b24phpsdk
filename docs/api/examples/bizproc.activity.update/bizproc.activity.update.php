<?php
declare(strict_types=1);

use Bitrix24\SDK\Tests\Integration\Fabric;

require_once 'tests/bootstrap.php';
$serviceBuilder = Fabric::getServiceBuilder(true);

//generated_example_code_start

try {
    $result = $serviceBuilder
        ->getBizProcScope()
        ->activity()
        ->update(
            'activity_code',
            'https://example.com/handler',
            1,
            ['en' => 'Activity Name', 'ru' => 'Название Активности'],
            ['en' => 'Activity Description', 'ru' => 'Описание Активности'],
            true,
            ['param1' => 'value1'],
            false,
            ['returnParam1' => 'value1'],
            null,
            null
        );

    if ($result->isSuccess()) {
        print($result->getCoreResponse()->getResponseData()->getResult()[0]);
    } else {
        print('Update failed.');
    }
} catch (Throwable $e) {
    print('Error: ' . $e->getMessage());
}

//generated_example_code_finish