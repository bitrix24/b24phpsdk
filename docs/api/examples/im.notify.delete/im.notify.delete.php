<?php
declare(strict_types=1);

use Bitrix24\SDK\Tests\Integration\Fabric;

require_once 'tests/bootstrap.php';
$serviceBuilder = Fabric::getServiceBuilder(true);

//generated_example_code_start

try {
    $notificationId = 123; // Replace with actual notification ID
    $notificationTag = null; // Replace with actual notification tag if needed
    $subTag = null; // Replace with actual sub tag if needed

    $result = $serviceBuilder->getIMScope()
        ->notify()
        ->delete($notificationId, $notificationTag, $subTag);

    if ($result->isSuccess()) {
        print($result->getCoreResponse()->getResponseData()->getResult()[0]);
    } else {
        print("Failed to delete notification.");
    }
} catch (Throwable $e) {
    print("An error occurred: " . $e->getMessage());
}

//generated_example_code_finish