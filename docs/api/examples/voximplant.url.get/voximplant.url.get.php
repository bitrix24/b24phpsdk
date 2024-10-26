<?php
declare(strict_types=1);

use Bitrix24\SDK\Tests\Integration\Fabric;

require_once 'tests/bootstrap.php';
$serviceBuilder = Fabric::getServiceBuilder(true);

//generated_example_code_start

try {
    $result = $serviceBuilder
        ->getTelephonyScope()
        ->voximplant()
        ->url()
        ->get();

    $pagesResult = $result->getPages();

    print($pagesResult->detail_statistics);
    print($pagesResult->buy_connector);
    print($pagesResult->edit_config);
    print($pagesResult->lines);
} catch (Throwable $e) {
    // Handle exception
    print($e->getMessage());
}

//generated_example_code_finish