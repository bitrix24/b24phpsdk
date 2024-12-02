<?php
declare(strict_types=1);

use Bitrix24\SDK\Tests\Integration\Fabric;

require_once 'tests/bootstrap.php';
$serviceBuilder = Fabric::getServiceBuilder(true);

//generated_example_code_start

```php
try {
    $id = 123; // Replace with the actual ID of the deal you want to delete
    $result = $serviceBuilder->getCRMScope()->deal()->delete($id);
    
    if ($result->isSuccess()) {
        print("Deal with ID {$id} has been successfully deleted.");
    } else {
        print("Failed to delete deal with ID {$id}.");
    }
} catch (Throwable $e) {
    print("An error occurred: " . $e->getMessage());
}
```

//generated_example_code_finish