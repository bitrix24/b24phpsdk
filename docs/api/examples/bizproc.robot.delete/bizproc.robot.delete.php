<?php
declare(strict_types=1);

use Bitrix24\SDK\Tests\Integration\Fabric;

require_once 'tests/bootstrap.php';
$serviceBuilder = Fabric::getServiceBuilder(true);

//generated_example_code_start

try {
    $robotCode = 'your_robot_code_here'; // Replace with the actual robot code
    $result = $serviceBuilder
        ->getBizProcScope()
        ->robot()
        ->delete($robotCode);

    if ($result->isSuccess()) {
        print("Robot deleted successfully.");
    } else {
        print("Failed to delete robot.");
    }
} catch (Throwable $e) {
    print("Error: " . $e->getMessage());
}

//generated_example_code_finish