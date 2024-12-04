<?php
declare(strict_types=1);

use Bitrix24\SDK\Tests\Integration\Fabric;

require_once 'tests/bootstrap.php';
$serviceBuilder = Fabric::getServiceBuilder(true);

//generated_example_code_start

```php
try {
    $userId = 123; // Example user ID
    $message = "This is a personal notification message.";
    $forEmailChannelMessage = null; // Optional
    $notificationTag = null; // Optional
    $subTag = null; // Optional
    $attachment = null; // Optional

    $result = $serviceBuilder
        ->getIMScope()
        ->notify()
        ->fromPersonal(
            $userId,
            $message,
            $forEmailChannelMessage,
            $notificationTag,
            $subTag,
            $attachment
        );

    print($result->getId());
} catch (Throwable $e) {
    // Handle the exception
    print("Error: " . $e->getMessage());
}
```

//generated_example_code_finish