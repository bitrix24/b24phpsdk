<?php
declare(strict_types=1);

use Bitrix24\SDK\Tests\Integration\Fabric;

require_once 'tests/bootstrap.php';
$serviceBuilder = Fabric::getServiceBuilder(true);

//generated_example_code_start

try {
    $productId = 1; // Example product ID
    $productService = $serviceBuilder->getCRMScope()->product();
    $productResult = $productService->get($productId);
    $itemResult = $productResult->product();
    
    print("ID: " . $itemResult->ID . "\n");
    print("Catalog ID: " . $itemResult->CATALOG_ID . "\n");
    print("Price: " . $itemResult->PRICE . "\n");
    print("Currency ID: " . $itemResult->CURRENCY_ID . "\n");
    print("Name: " . $itemResult->NAME . "\n");
    print("Code: " . $itemResult->CODE . "\n");
    print("Description: " . $itemResult->DESCRIPTION . "\n");
    print("Description Type: " . $itemResult->DESCRIPTION_TYPE . "\n");
    print("Active: " . $itemResult->ACTIVE . "\n");
    print("Section ID: " . $itemResult->SECTION_ID . "\n");
    print("Sort: " . $itemResult->SORT . "\n");
    print("VAT ID: " . $itemResult->VAT_ID . "\n");
    print("VAT Included: " . $itemResult->VAT_INCLUDED . "\n");
    print("Measure: " . $itemResult->MEASURE . "\n");
    print("XML ID: " . $itemResult->XML_ID . "\n");
    print("Preview Picture: " . $itemResult->PREVIEW_PICTURE . "\n");
    print("Detail Picture: " . $itemResult->DETAIL_PICTURE . "\n");
    print("Date Create: " . $itemResult->DATE_CREATE . "\n");
    print("Timestamp X: " . $itemResult->TIMESTAMP_X . "\n");
    print("Modified By: " . $itemResult->MODIFIED_BY . "\n");
    print("Created By: " . $itemResult->CREATED_BY . "\n");
} catch (\Throwable $e) {
    print("Error: " . $e->getMessage() . "\n");
}

//generated_example_code_finish