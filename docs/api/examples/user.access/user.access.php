<?php
declare(strict_types=1);

use Bitrix24\SDK\Tests\Integration\Fabric;

require_once 'tests/bootstrap.php';
$serviceBuilder = Fabric::getServiceBuilder(true);

//generated_example_code_start

try {
    $accessToCheck = ['permission1', 'permission2']; // Example permissions to check
    $response = $serviceBuilder->getMainScope()->checkUserAccess($accessToCheck);
    $result = $response->getResponseData();

    foreach ($result->getResult() as $item) {
        print($item);
    }
} catch (Throwable $exception) {
    print('Error: ' . $exception->getMessage());
}

//generated_example_code_finish