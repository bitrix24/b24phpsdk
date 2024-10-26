<?php
declare(strict_types=1);

use Bitrix24\SDK\Tests\Integration\Fabric;

require_once 'tests/bootstrap.php';
$serviceBuilder = Fabric::getServiceBuilder(true);

//generated_example_code_start

```php
try {
    $fieldsResult = $serviceBuilder
        ->getCRMScope()
        ->dealContact()
        ->fields();

    $fieldsDescription = $fieldsResult->getFieldsDescription();

    foreach ($fieldsDescription as $field) {
        if (isset($field['type']) && $field['type'] === 'datetime') {
            $field['value'] = (new DateTime())->format(DateTime::ATOM);
        }
        print($field['name'] . ': ' . $field['value'] . PHP_EOL);
    }
} catch (Throwable $e) {
    print('Error: ' . $e->getMessage());
}
```

//generated_example_code_finish