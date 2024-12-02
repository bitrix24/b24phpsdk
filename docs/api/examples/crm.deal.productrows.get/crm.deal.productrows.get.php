<?php
declare(strict_types=1);

use Bitrix24\SDK\Tests\Integration\Fabric;

require_once 'tests/bootstrap.php';
$serviceBuilder = Fabric::getServiceBuilder(true);

//generated_example_code_start

try {
    $dealId = 123; // Example deal ID
    $currency = null; // Example currency, can be set to a Money\Currency instance if needed

    $result = $serviceBuilder->getCRMScope()
        ->dealProductRows()
        ->get($dealId, $currency);

    foreach ($result->getProductRows() as $item) {
        print("ID: {$item->ID}\n");
        print("OWNER_ID: {$item->OWNER_ID}\n");
        print("OWNER_TYPE: {$item->OWNER_TYPE}\n");
        print("PRODUCT_ID: {$item->PRODUCT_ID}\n");
        print("PRODUCT_NAME: {$item->PRODUCT_NAME}\n");
        print("ORIGINAL_PRODUCT_NAME: {$item->ORIGINAL_PRODUCT_NAME}\n");
        print("PRODUCT_DESCRIPTION: {$item->PRODUCT_DESCRIPTION}\n");
        print("PRICE: {$item->PRICE}\n");
        print("PRICE_EXCLUSIVE: {$item->PRICE_EXCLUSIVE}\n");
        print("PRICE_NETTO: {$item->PRICE_NETTO}\n");
        print("PRICE_BRUTTO: {$item->PRICE_BRUTTO}\n");
        print("PRICE_ACCOUNT: {$item->PRICE_ACCOUNT}\n");
        print("QUANTITY: {$item->QUANTITY}\n");
        print("DISCOUNT_TYPE_ID: {$item->DISCOUNT_TYPE_ID}\n");
        print("DISCOUNT_RATE: {$item->DISCOUNT_RATE}\n");
        print("DISCOUNT_SUM: {$item->DISCOUNT_SUM}\n");
        print("TAX_RATE: {$item->TAX_RATE}\n");
        print("TAX_INCLUDED: {$item->TAX_INCLUDED}\n");
        print("CUSTOMIZED: {$item->CUSTOMIZED}\n");
        print("MEASURE_CODE: {$item->MEASURE_CODE}\n");
        print("MEASURE_NAME: {$item->MEASURE_NAME}\n");
        print("SORT: {$item->SORT}\n");
        print("XML_ID: {$item->XML_ID}\n");
        print("TYPE: {$item->TYPE}\n");
        print("STORE_ID: {$item->STORE_ID}\n");
        print("RESERVE_ID: {$item->RESERVE_ID}\n");
        print("DATE_RESERVE_END: {$item->DATE_RESERVE_END?->format(DATE_ATOM)}\n");
        print("RESERVE_QUANTITY: {$item->RESERVE_QUANTITY}\n");
    }
} catch (Throwable $e) {
    // Handle the exception
    print("Error: " . $e->getMessage());
}

//generated_example_code_finish