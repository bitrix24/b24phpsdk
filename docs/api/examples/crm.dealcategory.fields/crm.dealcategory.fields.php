<?php
declare(strict_types=1);

use Bitrix24\SDK\Tests\Integration\Fabric;

require_once 'tests/bootstrap.php';
$serviceBuilder = Fabric::getServiceBuilder(true);

//generated_example_code_start

```php
try {
    $fieldsResult = $serviceBuilder->getCRMScope()->dealCategory()->fields();
    $fields = $fieldsResult->getFieldsDescription();

    // Example of setting fields for a new deal category
    $newCategoryFields = [
        'CREATED_DATE' => (new DateTime())->format(DateTime::ATOM),
        'NAME' => 'New Deal Category',
        'IS_LOCKED' => 'N',
        'SORT' => 100,
    ];

    // You can now use $newCategoryFields for further operations, such as adding a new deal category.
    print_r($fieldsResult->getFieldsDescription());
} catch (Throwable $e) {
    echo 'Error: ' . $e->getMessage();
}
```

//generated_example_code_finish