<?php
declare(strict_types=1);

use Bitrix24\SDK\Tests\Integration\Fabric;

require_once 'tests/bootstrap.php';
$serviceBuilder = Fabric::getServiceBuilder(true);

//generated_example_code_start

try {
    $fields = [
        'TITLE' => 'New Deal',
        'TYPE_ID' => 'GIG',
        'CATEGORY_ID' => '1',
        'STAGE_ID' => 'C1:NEW',
        'CURRENCY_ID' => 'USD',
        'OPPORTUNITY' => '10000',
        'BEGINDATE' => (new DateTime())->format(DateTime::ATOM),
        'CLOSEDATE' => (new DateTime('+1 month'))->format(DateTime::ATOM),
        'COMMENTS' => 'This is a test deal.',
    ];

    $params = [
        'REGISTER_SONET_EVENT' => 'Y',
    ];

    $result = $serviceBuilder
        ->getCRMScope()
        ->deal()
        ->add($fields, $params);

    print($result->getId());

} catch (Throwable $e) {
    print('Error: ' . $e->getMessage());
}

//generated_example_code_finish