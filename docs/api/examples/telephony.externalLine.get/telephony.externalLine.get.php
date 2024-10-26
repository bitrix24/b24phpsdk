<?php
declare(strict_types=1);

use Bitrix24\SDK\Tests\Integration\Fabric;

require_once 'tests/bootstrap.php';
$serviceBuilder = Fabric::getServiceBuilder(true);

//generated_example_code_start

try {
    $externalLineService = $serviceBuilder->getTelephonyScope()->externalLine();
    $result = $externalLineService->get();
    $externalLines = $result->getExternalLines();

    foreach ($externalLines as $itemResult) {
        print($itemResult->lineNumber);
        print($itemResult->lineName);
        print($itemResult->isAutoCreateCrmEntities);
        // Assuming there are DateTime fields in the itemResult
        if ($itemResult->createdDate instanceof DateTime) {
            print($itemResult->createdDate->format(DateTime::ATOM));
        }
    }
} catch (Throwable $e) {
    // Handle exception
    print("Error: " . $e->getMessage());
}

//generated_example_code_finish