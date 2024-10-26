<?php
declare(strict_types=1);

use Bitrix24\SDK\Tests\Integration\Fabric;

require_once 'tests/bootstrap.php';
$serviceBuilder = Fabric::getServiceBuilder(true);

//generated_example_code_start

try {
    $fields = [
        'NAME' => 'New Deal Category',
        'CREATED_DATE' => (new DateTime())->format(DateTime::ATOM),
        'IS_LOCKED' => 'N',
        'SORT' => 100,
    ];

    $result = $serviceBuilder
        ->getCRMScope()
        ->dealCategory()
        ->add($fields);

    print($result->getId());
} catch (Throwable $e) {
    print($e->getMessage());
}

//generated_example_code_finish