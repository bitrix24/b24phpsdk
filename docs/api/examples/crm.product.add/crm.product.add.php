<?php
declare(strict_types=1);

use Bitrix24\SDK\Tests\Integration\Fabric;

require_once 'tests/bootstrap.php';
$serviceBuilder = Fabric::getServiceBuilder(true);

//generated_example_code_start

try {
    $fields = [
        'NAME' => 'Sample Product',
        'PRICE' => '100.00',
        'CURRENCY_ID' => 'USD',
        'ACTIVE' => 'Y',
        'DATE_CREATE' => (new DateTime())->format(DateTime::ATOM),
        'TIMESTAMP_X' => (new DateTime())->format(DateTime::ATOM),
        'CREATED_BY' => 1,
        'MODIFIED_BY' => 1,
        'CATALOG_ID' => 1,
        'DESCRIPTION' => 'This is a sample product.',
        'VAT_ID' => 1,
        'VAT_INCLUDED' => 'Y',
        'MEASURE' => 1,
        'SECTION_ID' => 1,
        'SORT' => 100,
        'XML_ID' => 'sample_product_001',
    ];

    $result = $serviceBuilder->getCRMScope()->product()->add($fields);
    print($result->getId());
} catch (Throwable $e) {
    print("Error: " . $e->getMessage());
}

//generated_example_code_finish