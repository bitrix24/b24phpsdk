<?php
declare(strict_types=1);

use Bitrix24\SDK\Tests\Integration\Fabric;

require_once 'tests/bootstrap.php';
$serviceBuilder = Fabric::getServiceBuilder(true);

//generated_example_code_start

try {
    $fieldsResult = $serviceBuilder
        ->getUserScope()
        ->fields();

    $fieldsDescription = $fieldsResult->getFieldsDescription();

    foreach ($fieldsDescription as $key => $value) {
        if ($value['type'] === 'datetime') {
            $value['value'] = (new DateTime($value['value']))->format(DateTime::ATOM);
        }
        print("Field: $key, Value: " . print_r($value, true));
    }
} catch (Throwable $e) {
    print("Error: " . $e->getMessage());
}

//generated_example_code_finish