<?php
declare(strict_types=1);

use Bitrix24\SDK\Tests\Integration\Fabric;

require_once 'tests/bootstrap.php';
$serviceBuilder = Fabric::getServiceBuilder(true);

//generated_example_code_start

try {
    $notificationId = 123; // Replace with your actual notification ID
    $answerText = "This is an answer text"; // Replace with your actual answer text

    $result = $serviceBuilder
        ->getIMScope()
        ->notify()
        ->answer($notificationId, $answerText);

    if ($result->isSuccess()) {
        print($result->getCoreResponse()->getResponseData()->getResult()[0]);
    } else {
        print("Failed to send answer.");
    }
} catch (Throwable $e) {
    print("An error occurred: " . $e->getMessage());
}

//generated_example_code_finish