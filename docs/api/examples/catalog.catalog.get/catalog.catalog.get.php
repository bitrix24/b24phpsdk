<?php
declare(strict_types=1);

use Bitrix24\SDK\Tests\Integration\Fabric;

require_once 'tests/bootstrap.php';
$serviceBuilder = Fabric::getServiceBuilder(true);

//generated_example_code_start

```php
try {
    $catalogId = 1; // Replace with the actual catalog ID
    $result = $serviceBuilder->getCatalogScope()
        ->catalog()
        ->get($catalogId);
    
    $catalogItem = $result->catalog();
    
    print("Iblock ID: " . $catalogItem->iblockId . PHP_EOL);
    print("Iblock Type ID: " . $catalogItem->iblockTypeId . PHP_EOL);
    print("ID: " . $catalogItem->id . PHP_EOL);
    print("LID: " . $catalogItem->lid . PHP_EOL);
    print("Name: " . $catalogItem->name . PHP_EOL);
    print("Product Iblock ID: " . $catalogItem->productIblockId . PHP_EOL);
    print("SKU Property ID: " . $catalogItem->skuPropertyId . PHP_EOL);
    print("Subscription: " . ($catalogItem->subscription ? 'Yes' : 'No') . PHP_EOL);
    print("VAT ID: " . $catalogItem->vatId . PHP_EOL);
    print("Yandex Export: " . ($catalogItem->yandexExport ? 'Yes' : 'No') . PHP_EOL);
} catch (Throwable $e) {
    print("An error occurred: " . $e->getMessage() . PHP_EOL);
}
```

//generated_example_code_finish