<?php
declare(strict_types=1);

use Bitrix24\SDK\Tests\Integration\Fabric;

require_once 'tests/bootstrap.php';
$serviceBuilder = Fabric::getServiceBuilder(true);

//generated_example_code_start

try {
    $fieldsResult = $serviceBuilder
        ->getCRMScope()
        ->lead()
        ->fields();

    $fieldsDescription = $fieldsResult->getFieldsDescription();

    // Assuming you want to print the fields description
    print_r($fieldsDescription);
} catch (Throwable $e) {
    print("Error: " . $e->getMessage());
}

//generated_example_code_finish