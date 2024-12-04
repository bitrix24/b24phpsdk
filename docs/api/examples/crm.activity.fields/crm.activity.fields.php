<?php
declare(strict_types=1);

use Bitrix24\SDK\Tests\Integration\Fabric;

require_once 'tests/bootstrap.php';
$serviceBuilder = Fabric::getServiceBuilder(true);

//generated_example_code_start

```php
try {
    $fieldsResult = $serviceBuilder->getCRMScope()->activity()->fields();
    $fields = $fieldsResult->getFieldsDescription();

    // Example of fields to be used, including DateTime fields formatted in Atom
    $activityFields = [
        'OWNER_ID' => 1,
        'OWNER_TYPE_ID' => '2',
        'TYPE_ID' => '3',
        'SUBJECT' => 'Meeting',
        'START_TIME' => (new DateTime())->format(DateTime::ATOM),
        'END_TIME' => (new DateTime('+1 hour'))->format(DateTime::ATOM),
        'DESCRIPTION' => 'Discuss project updates',
        'RESPONSIBLE_ID' => '4',
        'STATUS' => 'completed',
    ];

    // Process the fields using the add method if needed
    // $addResult = $serviceBuilder->getCRMScope()->activity()->add($activityFields);

    // Print out the result of the fields
    print_r($fields);
} catch (Throwable $e) {
    echo 'Error: ' . $e->getMessage();
}
```

//generated_example_code_finish