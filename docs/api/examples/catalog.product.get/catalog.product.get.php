<?php
declare(strict_types=1);

use Bitrix24\SDK\Tests\Integration\Fabric;

require_once 'tests/bootstrap.php';
$serviceBuilder = Fabric::getServiceBuilder(true);

//generated_example_code_start

try {
    $productId = 123; // Replace with the actual product ID you want to retrieve
    $productResult = $serviceBuilder->getCatalogScope()
        ->product()
        ->get($productId);

    $itemResult = $productResult->product();

    print("Active: " . ($itemResult->active ? 'Yes' : 'No') . PHP_EOL);
    print("Available: " . ($itemResult->available ? 'Yes' : 'No') . PHP_EOL);
    print("Bundle: " . ($itemResult->bundle ? 'Yes' : 'No') . PHP_EOL);
    print("Code: " . $itemResult->code . PHP_EOL);
    print("Created By: " . $itemResult->createdBy . PHP_EOL);
    print("Date Active From: " . ($itemResult->dateActiveFrom ? $itemResult->dateActiveFrom->format(DATE_ATOM) : 'N/A') . PHP_EOL);
    print("Date Active To: " . ($itemResult->dateActiveTo ? $itemResult->dateActiveTo->format(DATE_ATOM) : 'N/A') . PHP_EOL);
    print("Date Created: " . $itemResult->dateCreate->format(DATE_ATOM) . PHP_EOL);
    print("Name: " . $itemResult->name . PHP_EOL);
    print("ID: " . $itemResult->id . PHP_EOL);
    print("Iblock ID: " . $itemResult->iblockId . PHP_EOL);
    print("Iblock Section ID: " . $itemResult->iblockSectionId . PHP_EOL);
    print("Modified By: " . $itemResult->modifiedBy . PHP_EOL);
    print("Timestamp: " . $itemResult->timestampX->format(DATE_ATOM) . PHP_EOL);
    print("XML ID: " . $itemResult->xmlId . PHP_EOL);
} catch (Throwable $e) {
    print("An error occurred: " . $e->getMessage() . PHP_EOL);
}

//generated_example_code_finish