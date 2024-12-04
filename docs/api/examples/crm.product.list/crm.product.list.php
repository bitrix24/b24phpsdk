<?php
declare(strict_types=1);

use Bitrix24\SDK\Tests\Integration\Fabric;

require_once 'tests/bootstrap.php';
$serviceBuilder = Fabric::getServiceBuilder(true);

//generated_example_code_start

try {
    $order = []; // Define your order array
    $filter = []; // Define your filter array
    $select = ['ID', 'CATALOG_ID', 'PRICE', 'CURRENCY_ID', 'NAME', 'CODE', 'DESCRIPTION', 'DESCRIPTION_TYPE', 'ACTIVE', 'SECTION_ID', 'SORT', 'VAT_ID', 'VAT_INCLUDED', 'MEASURE', 'XML_ID', 'PREVIEW_PICTURE', 'DETAIL_PICTURE', 'DATE_CREATE', 'TIMESTAMP_X', 'MODIFIED_BY', 'CREATED_BY'];
    $startItem = 0; // Define your start item

    $result = $serviceBuilder
        ->getCRMScope()
        ->product()
        ->list($order, $filter, $select, $startItem);

    foreach ($result->getProducts() as $product) {
        print("ID: {$product->ID}\n");
        print("Catalog ID: {$product->CATALOG_ID}\n");
        print("Price: {$product->PRICE}\n");
        print("Currency ID: {$product->CURRENCY_ID}\n");
        print("Name: {$product->NAME}\n");
        print("Code: {$product->CODE}\n");
        print("Description: {$product->DESCRIPTION}\n");
        print("Description Type: {$product->DESCRIPTION_TYPE}\n");
        print("Active: {$product->ACTIVE}\n");
        print("Section ID: {$product->SECTION_ID}\n");
        print("Sort: {$product->SORT}\n");
        print("VAT ID: {$product->VAT_ID}\n");
        print("VAT Included: {$product->VAT_INCLUDED}\n");
        print("Measure: {$product->MEASURE}\n");
        print("XML ID: {$product->XML_ID}\n");
        print("Preview Picture: {$product->PREVIEW_PICTURE}\n");
        print("Detail Picture: {$product->DETAIL_PICTURE}\n");
        print("Date Create: " . (new DateTime($product->DATE_CREATE))->format(DateTime::ATOM) . "\n");
        print("Timestamp X: " . (new DateTime($product->TIMESTAMP_X))->format(DateTime::ATOM) . "\n");
        print("Modified By: {$product->MODIFIED_BY}\n");
        print("Created By: {$product->CREATED_BY}\n");
    }
} catch (Throwable $e) {
    print("Error: " . $e->getMessage());
}

//generated_example_code_finish