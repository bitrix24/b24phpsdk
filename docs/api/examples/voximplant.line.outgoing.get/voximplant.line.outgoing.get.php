<?php
declare(strict_types=1);

use Bitrix24\SDK\Tests\Integration\Fabric;

require_once 'tests/bootstrap.php';
$serviceBuilder = Fabric::getServiceBuilder(true);

//generated_example_code_start

```php
try {
    $result = $serviceBuilder
        ->getTelephonyScope()
        ->voximplant()
        ->line()
        ->outgoingGet();

    $lineIdResult = $result->getLineId();
    print($lineIdResult->LINE_ID);
} catch (Throwable $e) {
    // Handle the exception
    print("Error: " . $e->getMessage());
}
```

//generated_example_code_finish