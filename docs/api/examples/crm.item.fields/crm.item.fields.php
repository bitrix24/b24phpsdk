<?php
declare(strict_types=1);

use Bitrix24\SDK\Tests\Integration\Fabric;

require_once 'tests/bootstrap.php';
$serviceBuilder = Fabric::getServiceBuilder(true);

//generated_example_code_start

```php
try {
    $entityTypeId = 1; // Example entity type ID
    $fieldsResult = $serviceBuilder->getCRMScope()->item()->fields($entityTypeId);

    // Process the result
    $fieldsDescription = $fieldsResult->getFieldsDescription();

    // Print the result
    print_r($fieldsDescription);
} catch (Throwable $e) {
    echo 'Error: ' . $e->getMessage();
}
```

//generated_example_code_finish