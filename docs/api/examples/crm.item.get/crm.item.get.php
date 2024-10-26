<?php
declare(strict_types=1);

use Bitrix24\SDK\Tests\Integration\Fabric;

require_once 'tests/bootstrap.php';
$serviceBuilder = Fabric::getServiceBuilder(true);

//generated_example_code_start

try {
    $entityTypeId = 1; // Example entity type ID
    $id = 123; // Example item ID

    $itemResult = $serviceBuilder
        ->getCRMScope()
        ->item()
        ->get($entityTypeId, $id);

    $item = $itemResult->item();

    print("ID: " . $item->id . PHP_EOL);
    print("XML ID: " . $item->xmlId . PHP_EOL);
    print("Title: " . $item->title . PHP_EOL);
    print("Created By: " . $item->createdBy . PHP_EOL);
    print("Updated By: " . $item->updatedBy . PHP_EOL);
    print("Moved By: " . $item->movedBy . PHP_EOL);
    print("Created Time: " . $item->createdTime->format(DATE_ATOM) . PHP_EOL);
    print("Updated Time: " . $item->updatedTime->format(DATE_ATOM) . PHP_EOL);
    print("Moved Time: " . $item->movedTime->format(DATE_ATOM) . PHP_EOL);
    print("Category ID: " . $item->categoryId . PHP_EOL);
    print("Opened: " . ($item->opened ? 'true' : 'false') . PHP_EOL);
    print("Previous Stage ID: " . $item->previousStageId . PHP_EOL);
    print("Begin Date: " . $item->begindate->format(DATE_ATOM) . PHP_EOL);
    print("Close Date: " . $item->closedate->format(DATE_ATOM) . PHP_EOL);
    print("Company ID: " . $item->companyId . PHP_EOL);
    print("Contact ID: " . $item->contactId . PHP_EOL);
    print("Opportunity: " . $item->opportunity . PHP_EOL);
    print("Is Manual Opportunity: " . ($item->isManualOpportunity ? 'true' : 'false') . PHP_EOL);
    print("Tax Value: " . $item->taxValue . PHP_EOL);
    print("Currency ID: " . $item->currencyId . PHP_EOL);
    print("Opportunity Account: " . $item->opportunityAccount . PHP_EOL);
    print("Tax Value Account: " . $item->taxValueAccount . PHP_EOL);
    print("Account Currency ID: " . $item->accountCurrencyId . PHP_EOL);
    print("My Company ID: " . $item->mycompanyId . PHP_EOL);
    print("Source ID: " . $item->sourceId . PHP_EOL);
    print("Source Description: " . $item->sourceDescription . PHP_EOL);
    print("Webform ID: " . $item->webformId . PHP_EOL);
    print("Assigned By ID: " . $item->assignedById . PHP_EOL);
    print("Last Activity By: " . $item->lastActivityBy . PHP_EOL);
    print("Last Activity Time: " . $item->lastActivityTime->format(DATE_ATOM) . PHP_EOL);
    print("UTM Source: " . $item->utmSource . PHP_EOL);
    print("UTM Medium: " . $item->utmMedium . PHP_EOL);
    print("UTM Campaign: " . $item->utmCampaign . PHP_EOL);
    print("UTM Content: " . $item->utmContent . PHP_EOL);
    print("UTM Term: " . $item->utmTerm . PHP_EOL);
    print("Observers: " . json_encode($item->observers) . PHP_EOL);
    print("Contact IDs: " . json_encode($item->contactIds) . PHP_EOL);
    print("Entity Type ID: " . $item->entityTypeId . PHP_EOL);
} catch (Throwable $e) {
    print("Error: " . $e->getMessage() . PHP_EOL);
}

//generated_example_code_finish