<?php
declare(strict_types=1);

use Bitrix24\SDK\Tests\Integration\Fabric;

require_once 'tests/bootstrap.php';
$serviceBuilder = Fabric::getServiceBuilder(true);

//generated_example_code_start

try {
    $response = $serviceBuilder->getMainScope()->getAvailableScope();
    $responseData = $response->getResponseData();

    foreach ($responseData->getResult() as $item) {
        print($item->somePublicProperty); // Replace somePublicProperty with actual public property names
    }
} catch (Throwable $exception) {
    // Handle the exception, e.g. log it or display an error message
    error_log($exception->getMessage());
}

//generated_example_code_finish