<?php
declare(strict_types=1);

use Bitrix24\SDK\Tests\Integration\Fabric;

require_once 'tests/bootstrap.php';
$serviceBuilder = Fabric::getServiceBuilder(true);

//generated_example_code_start

try {
    $fieldsResult = $serviceBuilder->getCRMScope()->product()->fields();
    $fieldsDescription = $fieldsResult->getFieldsDescription();

    foreach ($fieldsDescription as $field) {
        if (isset($field['DATE_CREATE'])) {
            $field['DATE_CREATE'] = (new DateTime($field['DATE_CREATE']))->format(DateTime::ATOM);
        }
        
        if (isset($field['TIMESTAMP_X'])) {
            $field['TIMESTAMP_X'] = (new DateTime($field['TIMESTAMP_X']))->format(DateTime::ATOM);
        }
        
        print($field['ID'] . ': ' . $field['NAME'] . PHP_EOL);
    }
} catch (Throwable $e) {
    print('Error: ' . $e->getMessage() . PHP_EOL);
}

//generated_example_code_finish