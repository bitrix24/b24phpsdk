<?php
declare(strict_types=1);

use Bitrix24\SDK\Tests\Integration\Fabric;

require_once 'tests/bootstrap.php';
$serviceBuilder = Fabric::getServiceBuilder(true);

//generated_example_code_start

```php
try {
    $settingsService = $serviceBuilder->getCRMScope()->settings();
    $settingsModeResult = $settingsService->modeGet();
    
    print($settingsModeResult->getModeId());
} catch (Throwable $e) {
    // Handle exception
    print('Error: ' . $e->getMessage());
}
```

//generated_example_code_finish