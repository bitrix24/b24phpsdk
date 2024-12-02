<?php
declare(strict_types=1);

use Bitrix24\SDK\Tests\Integration\Fabric;

require_once 'tests/bootstrap.php';
$serviceBuilder = Fabric::getServiceBuilder(true);

//generated_example_code_start

```php
try {
    $select = ['id', 'name', 'price', 'active', 'dateCreate', 'dateActiveFrom', 'dateActiveTo'];
    $filter = ['active' => true];
    $order = ['name' => 'ASC'];
    $start = 0;

    $catalogsResult = $serviceBuilder->getCatalogScope()->catalog()->list($select, $filter, $order, $start);
    $catalogItems = $catalogsResult->getCatalogs();

    foreach ($catalogItems as $item) {
        print("ID: " . $item->id . "\n");
        print("Name: " . $item->name . "\n");
        print("Price: " . ($item->purchasingPrice ? $item->purchasingPrice->getAmount() : 'N/A') . "\n");
        print("Active: " . ($item->active ? 'Yes' : 'No') . "\n");
        print("Created Date: " . $item->dateCreate->format(DATE_ATOM) . "\n");
        if ($item->dateActiveFrom) {
            print("Active From: " . $item->dateActiveFrom->format(DATE_ATOM) . "\n");
        }
        if ($item->dateActiveTo) {
            print("Active To: " . $item->dateActiveTo->format(DATE_ATOM) . "\n");
        }
        print("\n");
    }
} catch (Throwable $e) {
    print("Error: " . $e->getMessage() . "\n");
}
```

//generated_example_code_finish