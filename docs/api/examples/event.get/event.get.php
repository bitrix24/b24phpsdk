<?php
declare(strict_types=1);

use Bitrix24\SDK\Tests\Integration\Fabric;

require_once 'tests/bootstrap.php';
$serviceBuilder = Fabric::getServiceBuilder(true);

//generated_example_code_start

try {
    $eventService = $serviceBuilder->getMainScope()->event();
    $result = $eventService->get();
    $eventHandlers = $result->getEventHandlers();

    foreach ($eventHandlers as $handler) {
        print("Event: " . $handler->event . "\n");
        print("Handler: " . $handler->handler . "\n");
        print("Auth Type: " . $handler->auth_type . "\n");
        print("Offline: " . $handler->offline . "\n");
    }
} catch (Throwable $e) {
    print("Error: " . $e->getMessage());
}

//generated_example_code_finish