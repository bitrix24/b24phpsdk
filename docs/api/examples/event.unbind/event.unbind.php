<?php
declare(strict_types=1);

use Bitrix24\SDK\Tests\Integration\Fabric;

require_once 'tests/bootstrap.php';
$serviceBuilder = Fabric::getServiceBuilder(true);

//generated_example_code_start

try {
    $eventCode = 'your_event_code'; // Replace with your actual event code
    $handlerUrl = 'https://your.handler.url'; // Replace with your actual handler URL
    $userId = null; // Replace with your actual user ID or leave as null

    $result = $serviceBuilder
        ->getMainScope()
        ->event()
        ->unbind($eventCode, $handlerUrl, $userId);

    print($result->getUnbindedHandlersCount());
} catch (Throwable $e) {
    print('Error: ' . $e->getMessage());
}

//generated_example_code_finish