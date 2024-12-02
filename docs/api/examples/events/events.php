<?php
declare(strict_types=1);

use Bitrix24\SDK\Tests\Integration\Fabric;

require_once 'tests/bootstrap.php';
$serviceBuilder = Fabric::getServiceBuilder(true);

//generated_example_code_start

try {
    $eventService = $serviceBuilder->getMainScope()->event();
    $result = $eventService->list($scopeCode);
    $events = $result->getEvents();

    foreach ($events as $event) {
        // Assuming the event object has public properties to print
        print($event->property1);
        print($event->property2);
        // Add more properties as needed
    }
} catch (Throwable $e) {
    // Handle exception
    print("An error occurred: " . $e->getMessage());
}

//generated_example_code_finish