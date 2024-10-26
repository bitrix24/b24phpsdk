<?php
declare(strict_types=1);

use Bitrix24\SDK\Tests\Integration\Fabric;

require_once 'tests/bootstrap.php';
$serviceBuilder = Fabric::getServiceBuilder(true);

//generated_example_code_start

try {
    $id = 123; // Example product ID
    $fields = [
        'ID' => $id,
        'CATALOG_ID' => 1,
        'PRICE' => '100.00',
        'CURRENCY_ID' => 'USD',
        'NAME' => 'Sample Product',
        'CODE' => 'sample_product',
        'DESCRIPTION' => 'This is a sample product.',
        'DESCRIPTION_TYPE' => 'text',
        'ACTIVE' => 'Y',
        'SECTION_ID' => 2,
        'SORT' => 100,
        'VAT_ID' => 3,
        'VAT_INCLUDED' => 'Y',
        'MEASURE' => 1,
        'XML_ID' => 'sample_xml_id',
        'PREVIEW_PICTURE' => null,
        'DETAIL_PICTURE' => null,
        'DATE_CREATE' => (new DateTime())->format(DateTime::ATOM),
        'TIMESTAMP_X' => (new DateTime())->format(DateTime::ATOM),
        'MODIFIED_BY' => 1,
        'CREATED_BY' => 1,
    ];

    $result = $serviceBuilder
        ->getCRMScope()
        ->product()
        ->update($id, $fields);

    if ($result->isSuccess()) {
        print($result->getCoreResponse()->getResponseData()->getResult()[0]);
    } else {
        print('Update failed.');
    }
} catch (Throwable $e) {
    print('Error: ' . $e->getMessage());
}

//generated_example_code_finish