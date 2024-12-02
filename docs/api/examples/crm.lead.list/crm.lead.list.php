<?php
declare(strict_types=1);

use Bitrix24\SDK\Tests\Integration\Fabric;

require_once 'tests/bootstrap.php';
$serviceBuilder = Fabric::getServiceBuilder(true);

//generated_example_code_start

try {
    $order = [];
    $filter = []; // Define your filter criteria here
    $select = [
        'ID', 'TITLE', 'HONORIFIC', 'NAME', 'SECOND_NAME', 'LAST_NAME', 
        'BIRTHDATE', 'COMPANY_TITLE', 'SOURCE_ID', 'SOURCE_DESCRIPTION', 
        'STATUS_ID', 'STATUS_DESCRIPTION', 'STATUS_SEMANTIC_ID', 'POST', 
        'ADDRESS', 'ADDRESS_2', 'ADDRESS_CITY', 'ADDRESS_POSTAL_CODE', 
        'ADDRESS_REGION', 'ADDRESS_PROVINCE', 'ADDRESS_COUNTRY', 
        'ADDRESS_COUNTRY_CODE', 'ADDRESS_LOC_ADDR_ID', 'CURRENCY_ID', 
        'OPPORTUNITY', 'IS_MANUAL_OPPORTUNITY', 'OPENED', 'COMMENTS', 
        'HAS_PHONE', 'HAS_EMAIL', 'HAS_IMOL', 'ASSIGNED_BY_ID', 
        'CREATED_BY_ID', 'MODIFY_BY_ID', 'MOVED_BY_ID', 'DATE_CREATE', 
        'DATE_MODIFY', 'MOVED_TIME', 'COMPANY_ID', 'CONTACT_ID', 
        'CONTACT_IDS', 'IS_RETURN_CUSTOMER', 'DATE_CLOSED', 
        'ORIGINATOR_ID', 'ORIGIN_ID', 'UTM_SOURCE', 'UTM_MEDIUM', 
        'UTM_CAMPAIGN', 'UTM_CONTENT', 'UTM_TERM', 'PHONE', 'EMAIL', 
        'WEB', 'IM', 'LINK'
    ];
    $startItem = 0;

    $leadsResult = $serviceBuilder->getCRMScope()->lead()->list($order, $filter, $select, $startItem);
    
    foreach ($leadsResult->getLeads() as $lead) {
        print("ID: {$lead->ID}, TITLE: {$lead->TITLE}, NAME: {$lead->NAME}, BIRTHDATE: " . 
              ($lead->BIRTHDATE ? $lead->BIRTHDATE->format(DATE_ATOM) : 'N/A') . "\n");
    }
} catch (Throwable $e) {
    print("Error: " . $e->getMessage());
}

//generated_example_code_finish