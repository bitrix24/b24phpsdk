<?php
declare(strict_types=1);

use Bitrix24\SDK\Tests\Integration\Fabric;

require_once 'tests/bootstrap.php';
$serviceBuilder = Fabric::getServiceBuilder(true);

//generated_example_code_start

try {
    $lineNumber = '8-9938-832799312'; // Example line number
    $result = $serviceBuilder
        ->getTelephonyScope()
        ->externalLine()
        ->delete($lineNumber);
    
    // Process result
    print($result->getRawData());
} catch (Throwable $e) {
    print("Error: " . $e->getMessage());
}

//generated_example_code_finish