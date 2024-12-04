<?php
declare(strict_types=1);

use Bitrix24\SDK\Tests\Integration\Fabric;

require_once 'tests/bootstrap.php';
$serviceBuilder = Fabric::getServiceBuilder(true);

//generated_example_code_start

try {
    $activityCode = 'your_activity_code_here'; // Replace with the actual activity code
    $result = $serviceBuilder
        ->getBizProcScope()
        ->activity()
        ->delete($activityCode);

    if ($result->isSuccess()) {
        print("Activity with code '{$activityCode}' deleted successfully.");
    } else {
        print("Failed to delete activity: " . json_encode($result->getErrorMessages()));
    }
} catch (Throwable $e) {
    print("An error occurred: " . $e->getMessage());
}

//generated_example_code_finish