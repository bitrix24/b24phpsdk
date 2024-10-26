<?php
declare(strict_types=1);

use Bitrix24\SDK\Tests\Integration\Fabric;

require_once 'tests/bootstrap.php';
$serviceBuilder = Fabric::getServiceBuilder(true);

//generated_example_code_start

try {
    $userId = 123; // Example user ID
    $result = $serviceBuilder->getTelephonyScope()
        ->getVoximplantScope()
        ->getUserService()
        ->activatePhone($userId);

    if ($result->isSuccess()) {
        print("Phone activated successfully for user ID: " . $userId);
    } else {
        print("Failed to activate phone for user ID: " . $userId);
    }
} catch (Throwable $e) {
    print("An error occurred: " . $e->getMessage());
}

//generated_example_code_finish