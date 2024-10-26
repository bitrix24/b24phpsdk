<?php
declare(strict_types=1);

use Bitrix24\SDK\Tests\Integration\Fabric;

require_once 'tests/bootstrap.php';
$serviceBuilder = Fabric::getServiceBuilder(true);

//generated_example_code_start

```php
try {
    $result = $serviceBuilder
        ->getCRMScope()
        ->userfield()
        ->enumerationFields();

    $fields = $result->getFieldsDescription();

    foreach ($fields as $field) {
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