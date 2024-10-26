<?php
declare(strict_types=1);

use Bitrix24\SDK\Tests\Integration\Fabric;

require_once 'tests/bootstrap.php';
$serviceBuilder = Fabric::getServiceBuilder(true);

//generated_example_code_start

try {
    $order = [];
    $filter = [];
    $select = ['ID', 'HONORIFIC', 'NAME', 'SECOND_NAME', 'LAST_NAME', 'PHOTO', 'BIRTHDATE', 'TYPE_ID', 'SOURCE_ID', 'SOURCE_DESCRIPTION', 'POST', 'ADDRESS', 'ADDRESS_2', 'ADDRESS_CITY', 'ADDRESS_POSTAL_CODE', 'ADDRESS_REGION', 'ADDRESS_PROVINCE', 'ADDRESS_COUNTRY', 'ADDRESS_COUNTRY_CODE', 'ADDRESS_LOC_ADDR_ID', 'COMMENTS', 'OPENED', 'EXPORT', 'HAS_PHONE', 'HAS_EMAIL', 'HAS_IMOL', 'ASSIGNED_BY_ID', 'CREATED_BY_ID', 'MODIFY_BY_ID', 'DATE_CREATE', 'DATE_MODIFY', 'COMPANY_ID', 'COMPANY_IDS', 'LEAD_ID', 'ORIGINATOR_ID', 'ORIGIN_ID', 'ORIGIN_VERSION', 'FACE_ID', 'UTM_SOURCE', 'UTM_MEDIUM', 'UTM_CAMPAIGN', 'UTM_CONTENT', 'UTM_TERM', 'PHONE', 'EMAIL', 'WEB', 'IM'];
    $start = 0;

    $contactsResult = $serviceBuilder->getCRMScope()->contact()->list($order, $filter, $select, $start);

    foreach ($contactsResult->getContacts() as $contact) {
        print("ID: {$contact->ID}\n");
        print("Honorific: {$contact->HONORIFIC}\n");
        print("Name: {$contact->NAME}\n");
        print("Second Name: {$contact->SECOND_NAME}\n");
        print("Last Name: {$contact->LAST_NAME}\n");
        print("Photo: {$contact->PHOTO}\n");
        print("Birthdate: {$contact->BIRTHDATE->format(DATE_ATOM)}\n");
        print("Type ID: {$contact->TYPE_ID}\n");
        print("Source ID: {$contact->SOURCE_ID}\n");
        print("Source Description: {$contact->SOURCE_DESCRIPTION}\n");
        print("Post: {$contact->POST}\n");
        print("Address: {$contact->ADDRESS}\n");
        print("Address 2: {$contact->ADDRESS_2}\n");
        print("Address City: {$contact->ADDRESS_CITY}\n");
        print("Address Postal Code: {$contact->ADDRESS_POSTAL_CODE}\n");
        print("Address Region: {$contact->ADDRESS_REGION}\n");
        print("Address Province: {$contact->ADDRESS_PROVINCE}\n");
        print("Address Country: {$contact->ADDRESS_COUNTRY}\n");
        print("Address Country Code: {$contact->ADDRESS_COUNTRY_CODE}\n");
        print("Address Location Address ID: {$contact->ADDRESS_LOC_ADDR_ID}\n");
        print("Comments: {$contact->COMMENTS}\n");
        print("Opened: {$contact->OPENED}\n");
        print("Export: {$contact->EXPORT}\n");
        print("Has Phone: {$contact->HAS_PHONE}\n");
        print("Has Email: {$contact->HAS_EMAIL}\n");
        print("Has IMOL: {$contact->HAS_IMOL}\n");
        print("Assigned By ID: {$contact->ASSIGNED_BY_ID}\n");
        print("Created By ID: {$contact->CREATED_BY_ID}\n");
        print("Modified By ID: {$contact->MODIFY_BY_ID}\n");
        print("Date Created: {$contact->DATE_CREATE->format(DATE_ATOM)}\n");
        print("Date Modified: {$contact->DATE_MODIFY->format(DATE_ATOM)}\n");
        print("Company ID: {$contact->COMPANY_ID}\n");
        print("Company IDs: " . implode(',', $contact->COMPANY_IDS ?? []) . "\n");
        print("Lead ID: {$contact->LEAD_ID}\n");
        print("Originator ID: {$contact->ORIGINATOR_ID}\n");
        print("Origin ID: {$contact->ORIGIN_ID}\n");
        print("Origin Version: {$contact->ORIGIN_VERSION}\n");
        print("Face ID: {$contact->FACE_ID}\n");
        print("UTM Source: {$contact->UTM_SOURCE}\n");
        print("UTM Medium: {$contact->UTM_MEDIUM}\n");
        print("UTM Campaign: {$contact->UTM_CAMPAIGN}\n");
        print("UTM Content: {$contact->UTM_CONTENT}\n");
        print("UTM Term: {$contact->UTM_TERM}\n");
        print("Phone: " . implode(',', $contact->PHONE ?? []) . "\n");
        print("Email: " . implode(',', $contact->EMAIL ?? []) . "\n");
        print("Web: " . implode(',', $contact->WEB ?? []) . "\n");
        print("IM: " . implode(',', $contact->IM ?? []) . "\n");
        print("\n");
    }
} catch (Throwable $e) {
    print("Error: " . $e->getMessage());
}

//generated_example_code_finish