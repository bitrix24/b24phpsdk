<?php
declare(strict_types=1);

use Bitrix24\SDK\Tests\Integration\Fabric;

require_once 'tests/bootstrap.php';
$serviceBuilder = Fabric::getServiceBuilder(true);

//generated_example_code_start

```php
try {
    $iblockId = 1; // Example iblockId
    $productType = new Bitrix24\SDK\Services\Catalog\Common\ProductType('product'); // Example product type
    $additionalFilter = null; // Example additional filter

    $result = $serviceBuilder
        ->getCatalogScope()
        ->product()
        ->fieldsByFilter($iblockId, $productType, $additionalFilter);

    $fieldsResult = $result->getFieldsDescription();

    foreach ($fieldsResult as $field) {
        if (isset($field['date'])) {
            $field['date'] = (new DateTime($field['date']))->format(DateTime::ATOM);
        }
        print($field['name'] . ': ' . $field['value'] . PHP_EOL);
    }
} catch (Throwable $e) {
    print('Error: ' . $e->getMessage());
}
```

//generated_example_code_finish