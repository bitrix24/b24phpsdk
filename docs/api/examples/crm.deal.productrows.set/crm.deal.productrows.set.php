<?php
declare(strict_types=1);

use Bitrix24\SDK\Tests\Integration\Fabric;

require_once 'tests/bootstrap.php';
$serviceBuilder = Fabric::getServiceBuilder(true);

//generated_example_code_start

try {
    $dealId = 123; // Example deal ID
    $productRows = [
        [
            'ID' => 1,
            'OWNER_ID' => 123,
            'OWNER_TYPE' => 'D',
            'PRODUCT_ID' => 456,
            'PRODUCT_NAME' => 'Product 1',
            'PRICE' => '100.00',
            'PRICE_EXCLUSIVE' => '100.00',
            'PRICE_NETTO' => '100.00',
            'PRICE_BRUTTO' => '100.00',
            'QUANTITY' => '1',
            'DISCOUNT_TYPE_ID' => 1,
            'DISCOUNT_RATE' => '0',
            'DISCOUNT_SUM' => '0',
            'TAX_RATE' => '20',
            'TAX_INCLUDED' => 'Y',
            'CUSTOMIZED' => 'N',
            'MEASURE_CODE' => 1,
            'MEASURE_NAME' => 'pcs',
            'SORT' => 100,
        ],
        // Add more product rows as needed
    ];

    $result = $serviceBuilder
        ->getCRMScope()
        ->dealProductRows()
        ->set($dealId, $productRows);

    if ($result->isSuccess()) {
        print($result->getCoreResponse()->getResponseData()->getResult()[0]);
    } else {
        print("Failed to set product rows.");
    }
} catch (Throwable $e) {
    print("Error occurred: " . $e->getMessage());
}

//generated_example_code_finish