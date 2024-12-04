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
        ->contact()
        ->fields();

    $fields = [
        'ID' => 1,
        'HONORIFIC' => 'Mr.',
        'NAME' => 'John',
        'SECOND_NAME' => 'Doe',
        'LAST_NAME' => 'Smith',
        'BIRTHDATE' => (new DateTime('1990-01-01'))->format(DateTime::ATOM),
        'TYPE_ID' => 'CLIENT',
        'SOURCE_ID' => 'WEB',
        'SOURCE_DESCRIPTION' => 'Website Inquiry',
        'POST' => 'Manager',
        'ADDRESS' => '123 Main St',
        'ADDRESS_CITY' => 'Anytown',
        'ADDRESS_COUNTRY' => 'USA',
        'PHONE' => '+1234567890',
        'EMAIL' => 'john.doe@example.com',
    ];

    $result = $fieldsResult->getFieldsDescription();

    print_r($result);
} catch (Throwable $e) {
    echo 'Error: ' . $e->getMessage();
}
```

//generated_example_code_finish