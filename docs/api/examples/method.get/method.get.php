<?php
declare(strict_types=1);

use Bitrix24\SDK\Tests\Integration\Fabric;

require_once 'tests/bootstrap.php';
$serviceBuilder = Fabric::getServiceBuilder(true);

//generated_example_code_start

try {
    $methodName = 'desired.method.name'; // Replace with the actual method name you want to check
    $result = $serviceBuilder
        ->getMainScope()
        ->getMethodAffordability($methodName);

    print("Is Existing: " . ($result->isExisting() ? 'Yes' : 'No') . "\n");
    print("Is Available: " . ($result->isAvailable() ? 'Yes' : 'No') . "\n");
} catch (Throwable $e) {
    print("Error: " . $e->getMessage() . "\n");
}

//generated_example_code_finish