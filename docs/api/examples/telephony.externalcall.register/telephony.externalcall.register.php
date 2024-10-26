<?php
declare(strict_types=1);

use Bitrix24\SDK\Tests\Integration\Fabric;

require_once 'tests/bootstrap.php';
$serviceBuilder = Fabric::getServiceBuilder(true);

//generated_example_code_start

try {
    $callStartDate = new \Carbon\CarbonImmutable('2023-10-01T12:00:00+00:00'); // Example date in Atom format
    $callType = new \Bitrix24\SDK\Services\Telephony\Common\CallType(1); // Assuming 1 is outbound call type

    $result = $serviceBuilder
        ->getTelephonyScope()
        ->externalCall()
        ->register(
            '123456789', // non-empty-string $userInnerPhoneNumber
            1, // int<0, max> $b24UserId
            '987654321', // non-empty-string $phoneNumber
            $callStartDate, // Carbon\CarbonImmutable $callStartDate
            $callType, // Bitrix24\SDK\Services\Telephony\Common\CallType $callType
            true, // ?bool $isShowCallCard
            null, // ?bool|null $isCreateCrmEntities
            null, // ?(non-empty-string)|null $lineNumber
            null, // ?(non-empty-string)|null $sourceId
            null, // ?Bitrix24\SDK\Services\Telephony\Common\CrmEntityType|null $crmEntityType
            null, // ?int|null $crmEntityId
            null  // ?int|null $callListId
        );

    $itemResult = $result->getExternalCallRegistered()->getExternalCallRegistered();
    print("CALL_ID: " . $itemResult->CALL_ID . "\n");
    print("CRM_CREATED_LEAD: " . $itemResult->CRM_CREATED_LEAD . "\n");
    print("CRM_ENTITY_TYPE: " . $itemResult->CRM_ENTITY_TYPE . "\n");
    print("CRM_ENTITY_ID: " . $itemResult->CRM_ENTITY_ID . "\n");
    print("LEAD_CREATION_ERROR: " . $itemResult->LEAD_CREATION_ERROR . "\n");
    
} catch (\Throwable $e) {
    print("Error: " . $e->getMessage());
}

//generated_example_code_finish