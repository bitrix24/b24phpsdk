<?php
declare(strict_types=1);

use Bitrix24\SDK\Tests\Integration\Fabric;

require_once 'tests/bootstrap.php';
$serviceBuilder = Fabric::getServiceBuilder(true);

//generated_example_code_start

try {
    $serverTimeResult = $serviceBuilder
        ->getMainScope()
        ->getServerTime();

    $serverTime = $serverTimeResult->time()->format(DATE_ATOM);
    
    print("Server Time: " . $serverTime);
} catch (Throwable $e) {
    print("Error: " . $e->getMessage());
}

//generated_example_code_finish