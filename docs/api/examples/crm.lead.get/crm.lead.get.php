<?php
declare(strict_types=1);

use Bitrix24\SDK\Tests\Integration\Fabric;

require_once 'tests/bootstrap.php';
$serviceBuilder = Fabric::getServiceBuilder(true);

//generated_example_code_start

try {
    $id = 123; // Example lead ID
    $leadResult = $serviceBuilder->getCRMScope()->lead()->get($id);
    $leadItem = $leadResult->lead();
    
    print("ID: " . $leadItem->ID . "\n");
    print("TITLE: " . $leadItem->TITLE . "\n");
    print("HONORIFIC: " . $leadItem->HONORIFIC . "\n");
    print("NAME: " . $leadItem->NAME . "\n");
    print("SECOND_NAME: " . $leadItem->SECOND_NAME . "\n");
    print("LAST_NAME: " . $leadItem->LAST_NAME . "\n");
    print("BIRTHDATE: " . ($leadItem->BIRTHDATE ? $leadItem->BIRTHDATE->format(DATE_ATOM) : 'N/A') . "\n");
    print("COMPANY_TITLE: " . $leadItem->COMPANY_TITLE . "\n");
    print("SOURCE_ID: " . $leadItem->SOURCE_ID . "\n");
    print("SOURCE_DESCRIPTION: " . $leadItem->SOURCE_DESCRIPTION . "\n");
    print("STATUS_ID: " . $leadItem->STATUS_ID . "\n");
    print("STATUS_DESCRIPTION: " . $leadItem->STATUS_DESCRIPTION . "\n");
    print("STATUS_SEMANTIC_ID: " . $leadItem->STATUS_SEMANTIC_ID . "\n");
    print("POST: " . $leadItem->POST . "\n");
    print("ADDRESS: " . $leadItem->ADDRESS . "\n");
    print("ADDRESS_2: " . $leadItem->ADDRESS_2 . "\n");
    print("ADDRESS_CITY: " . $leadItem->ADDRESS_CITY . "\n");
    print("ADDRESS_POSTAL_CODE: " . $leadItem->ADDRESS_POSTAL_CODE . "\n");
    print("ADDRESS_REGION: " . $leadItem->ADDRESS_REGION . "\n");
    print("ADDRESS_PROVINCE: " . $leadItem->ADDRESS_PROVINCE . "\n");
    print("ADDRESS_COUNTRY: " . $leadItem->ADDRESS_COUNTRY . "\n");
    print("ADDRESS_COUNTRY_CODE: " . $leadItem->ADDRESS_COUNTRY_CODE . "\n");
    print("ADDRESS_LOC_ADDR_ID: " . $leadItem->ADDRESS_LOC_ADDR_ID . "\n");
    print("CURRENCY_ID: " . $leadItem->CURRENCY_ID . "\n");
    print("OPPORTUNITY: " . $leadItem->OPPORTUNITY . "\n");
    print("IS_MANUAL_OPPORTUNITY: " . $leadItem->IS_MANUAL_OPPORTUNITY . "\n");
    print("OPENED: " . $leadItem->OPENED . "\n");
    print("COMMENTS: " . $leadItem->COMMENTS . "\n");
    print("HAS_PHONE: " . $leadItem->HAS_PHONE . "\n");
    print("HAS_EMAIL: " . $leadItem->HAS_EMAIL . "\n");
    print("HAS_IMOL: " . $leadItem->HAS_IMOL . "\n");
    print("ASSIGNED_BY_ID: " . $leadItem->ASSIGNED_BY_ID . "\n");
    print("CREATED_BY_ID: " . $leadItem->CREATED_BY_ID . "\n");
    print("MODIFY_BY_ID: " . $leadItem->MODIFY_BY_ID . "\n");
    print("MOVED_BY_ID: " . $leadItem->MOVED_BY_ID . "\n");
    print("DATE_CREATE: " . ($leadItem->DATE_CREATE ? $leadItem->DATE_CREATE->format(DATE_ATOM) : 'N/A') . "\n");
    print("DATE_MODIFY: " . ($leadItem->DATE_MODIFY ? $leadItem->DATE_MODIFY->format(DATE_ATOM) : 'N/A') . "\n");
    print("MOVED_TIME: " . ($leadItem->MOVED_TIME ? $leadItem->MOVED_TIME->format(DATE_ATOM) : 'N/A') . "\n");
    print("COMPANY_ID: " . $leadItem->COMPANY_ID . "\n");
    print("CONTACT_ID: " . $leadItem->CONTACT_ID . "\n");
    print("CONTACT_IDS: " . $leadItem->CONTACT_IDS . "\n");
    print("IS_RETURN_CUSTOMER: " . $leadItem->IS_RETURN_CUSTOMER . "\n");
    print("DATE_CLOSED: " . ($leadItem->DATE_CLOSED ? $leadItem->DATE_CLOSED->format(DATE_ATOM) : 'N/A') . "\n");
    print("ORIGINATOR_ID: " . $leadItem->ORIGINATOR_ID . "\n");
    print("ORIGIN_ID: " . $leadItem->ORIGIN_ID . "\n");
    print("UTM_SOURCE: " . $leadItem->UTM_SOURCE . "\n");
    print("UTM_MEDIUM: " . $leadItem->UTM_MEDIUM . "\n");
    print("UTM_CAMPAIGN: " . $leadItem->UTM_CAMPAIGN . "\n");
    print("UTM_CONTENT: " . $leadItem->UTM_CONTENT . "\n");
    print("UTM_TERM: " . $leadItem->UTM_TERM . "\n");
    print("PHONE: " . json_encode($leadItem->PHONE) . "\n");
    print("EMAIL: " . json_encode($leadItem->EMAIL) . "\n");
    print("WEB: " . json_encode($leadItem->WEB) . "\n");
    print("IM: " . json_encode($leadItem->IM) . "\n");
    print("LINK: " . $leadItem->LINK . "\n");
} catch (Throwable $e) {
    print("Error: " . $e->getMessage() . "\n");
}

//generated_example_code_finish