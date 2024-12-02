<?php
declare(strict_types=1);

use Bitrix24\SDK\Tests\Integration\Fabric;

require_once 'tests/bootstrap.php';
$serviceBuilder = Fabric::getServiceBuilder(true);

//generated_example_code_start

```php
try {
    $result = $serviceBuilder
        ->getCatalogScope()
        ->fields()
        ->getFieldsDescription();

    foreach ($result as $item) {
        print($item);
    }
} catch (Throwable $e) {
    // Handle the exception
    print($e->getMessage());
}
```

//generated_example_code_finish