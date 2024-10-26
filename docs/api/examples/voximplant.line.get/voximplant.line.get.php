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
        ->getVoximplantScope()
        ->getLineScope()
        ->get();

    foreach ($result->getLines() as $item) {
        print("LINE_ID: " . $item->LINE_ID . "\n");
        print("NUMBER: " . $item->NUMBER . "\n");
    }
} catch (Throwable $e) {
    // Handle exception
    print("Error: " . $e->getMessage());
}
```

//generated_example_code_finish