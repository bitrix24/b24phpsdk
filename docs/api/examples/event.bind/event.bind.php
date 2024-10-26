<?php
declare(strict_types=1);

use Bitrix24\SDK\Tests\Integration\Fabric;

require_once 'tests/bootstrap.php';
$serviceBuilder = Fabric::getServiceBuilder(true);

//generated_example_code_start

```php
try {
    $eventCode = 'your_event_code';
    $handlerUrl = 'https://your-handler-url.com';
    $userId = null; // or an integer value
    $options = null; // or an array of options

    $result = $serviceBuilder
        ->getMainScope()
        ->event()
        ->bind($eventCode, $handlerUrl, $userId, $options);

    // Process return result
    if ($result->isBinded()) {
        print("Event handler successfully bound.");
    } else {
        print("Failed to bind event handler.");
    }
} catch (Throwable $e) {
    print("An error occurred: " . $e->getMessage());
}
```

//generated_example_code_finish