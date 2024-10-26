<?php
declare(strict_types=1);

use Bitrix24\SDK\Tests\Integration\Fabric;

require_once 'tests/bootstrap.php';
$serviceBuilder = Fabric::getServiceBuilder(true);

//generated_example_code_start

try {
    $order = []; // Define your order array as needed
    $filter = []; // Define your filter array as needed
    $select = ['ID', 'TITLE', 'TYPE_ID', 'CATEGORY_ID', 'STAGE_ID', 'STAGE_SEMANTIC_ID', 'IS_NEW', 'IS_RECURRING', 'PROBABILITY', 'CURRENCY_ID', 'OPPORTUNITY', 'IS_MANUAL_OPPORTUNITY', 'TAX_VALUE', 'LEAD_ID', 'COMPANY_ID', 'CONTACT_ID', 'QUOTE_ID', 'BEGINDATE', 'CLOSEDATE', 'OPENED', 'CLOSED', 'COMMENTS', 'ADDITIONAL_INFO', 'LOCATION_ID', 'IS_RETURN_CUSTOMER', 'IS_REPEATED_APPROACH', 'SOURCE_ID', 'SOURCE_DESCRIPTION', 'ORIGINATOR_ID', 'ORIGIN_ID', 'UTM_SOURCE', 'UTM_MEDIUM', 'UTM_CAMPAIGN', 'UTM_CONTENT', 'UTM_TERM'];
    $startItem = 0; // Set start item as needed

    $dealsResult = $serviceBuilder->getCRMScope()->deal()->list($order, $filter, $select, $startItem);

    foreach ($dealsResult->getDeals() as $dealItem) {
        print("ID: {$dealItem->ID}\n");
        print("Title: {$dealItem->TITLE}\n");
        print("Type ID: {$dealItem->TYPE_ID}\n");
        print("Category ID: {$dealItem->CATEGORY_ID}\n");
        print("Stage ID: {$dealItem->STAGE_ID}\n");
        print("Stage Semantic ID: {$dealItem->STAGE_SEMANTIC_ID}\n");
        print("Is New: {$dealItem->IS_NEW}\n");
        print("Is Recurring: {$dealItem->IS_RECURRING}\n");
        print("Probability: {$dealItem->PROBABILITY}\n");
        print("Currency ID: {$dealItem->CURRENCY_ID}\n");
        print("Opportunity: {$dealItem->OPPORTUNITY}\n");
        print("Is Manual Opportunity: {$dealItem->IS_MANUAL_OPPORTUNITY}\n");
        print("Tax Value: {$dealItem->TAX_VALUE}\n");
        print("Lead ID: {$dealItem->LEAD_ID}\n");
        print("Company ID: {$dealItem->COMPANY_ID}\n");
        print("Contact ID: {$dealItem->CONTACT_ID}\n");
        print("Quote ID: {$dealItem->QUOTE_ID}\n");
        print("Begin Date: " . ($dealItem->BEGINDATE ? $dealItem->BEGINDATE->format(DATE_ATOM) : 'N/A') . "\n");
        print("Close Date: " . ($dealItem->CLOSEDATE ? $dealItem->CLOSEDATE->format(DATE_ATOM) : 'N/A') . "\n");
        print("Opened: {$dealItem->OPENED}\n");
        print("Closed: {$dealItem->CLOSED}\n");
        print("Comments: {$dealItem->COMMENTS}\n");
        print("Additional Info: {$dealItem->ADDITIONAL_INFO}\n");
        print("Location ID: {$dealItem->LOCATION_ID}\n");
        print("Is Return Customer: {$dealItem->IS_RETURN_CUSTOMER}\n");
        print("Is Repeated Approach: {$dealItem->IS_REPEATED_APPROACH}\n");
        print("Source ID: {$dealItem->SOURCE_ID}\n");
        print("Source Description: {$dealItem->SOURCE_DESCRIPTION}\n");
        print("Originator ID: {$dealItem->ORIGINATOR_ID}\n");
        print("Origin ID: {$dealItem->ORIGIN_ID}\n");
        print("UTM Source: {$dealItem->UTM_SOURCE}\n");
        print("UTM Medium: {$dealItem->UTM_MEDIUM}\n");
        print("UTM Campaign: {$dealItem->UTM_CAMPAIGN}\n");
        print("UTM Content: {$dealItem->UTM_CONTENT}\n");
        print("UTM Term: {$dealItem->UTM_TERM}\n");
    }
} catch (Throwable $e) {
    print("Error: " . $e->getMessage());
}

//generated_example_code_finish