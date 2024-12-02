<?php
declare(strict_types=1);

use Bitrix24\SDK\Tests\Integration\Fabric;

require_once 'tests/bootstrap.php';
$serviceBuilder = Fabric::getServiceBuilder(true);

//generated_example_code_start

try {
    $id = 123; // Example deal ID
    $dealService = $serviceBuilder->getCRMScope()->deal();
    $dealResult = $dealService->get($id);
    $itemResult = $dealResult->deal();

    print("ID: " . $itemResult->ID . PHP_EOL);
    print("Title: " . $itemResult->TITLE . PHP_EOL);
    print("Type ID: " . $itemResult->TYPE_ID . PHP_EOL);
    print("Category ID: " . $itemResult->CATEGORY_ID . PHP_EOL);
    print("Stage ID: " . $itemResult->STAGE_ID . PHP_EOL);
    print("Is New: " . ($itemResult->IS_NEW ? 'Yes' : 'No') . PHP_EOL);
    print("Is Recurring: " . ($itemResult->IS_RECURRING ? 'Yes' : 'No') . PHP_EOL);
    print("Probability: " . $itemResult->PROBABILITY . PHP_EOL);
    print("Currency ID: " . $itemResult->CURRENCY_ID . PHP_EOL);
    print("Opportunity: " . $itemResult->OPPORTUNITY . PHP_EOL);
    print("Lead ID: " . $itemResult->LEAD_ID . PHP_EOL);
    print("Company ID: " . $itemResult->COMPANY_ID . PHP_EOL);
    print("Begin Date: " . ($itemResult->BEGINDATE ? $itemResult->BEGINDATE->format(DATE_ATOM) : 'N/A') . PHP_EOL);
    print("Close Date: " . ($itemResult->CLOSEDATE ? $itemResult->CLOSEDATE->format(DATE_ATOM) : 'N/A') . PHP_EOL);
} catch (Throwable $e) {
    print("Error: " . $e->getMessage() . PHP_EOL);
}

//generated_example_code_finish