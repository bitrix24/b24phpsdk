<?php
declare(strict_types=1);

use Bitrix24\SDK\Tests\Integration\Fabric;

require_once 'tests/bootstrap.php';
$serviceBuilder = Fabric::getServiceBuilder(true);

//generated_example_code_start

try {
    // Assuming $serviceBuilder is already defined and is of type ServiceBuilder
    $externalCallService = $serviceBuilder->getTelephonyScope()->getExternalCall();

    // Define your parameters
    $callId = 'some-call-id'; // non-empty-string
    $b24UserId = 123; // int<1, max>
    $duration = 120; // int<0, max>
    $money = new Money(1000, new Currency('USD')); // Money\Money
    $telephonyCallStatusCode = new TelephonyCallStatusCode(1); // Assuming 1 is a valid status code
    $isAddCallToChat = true; // ?bool
    $failedReason = null; // ?(non-empty-string)|null
    $userVote = null; // ?null|int

    // Call the finishForUserId method
    $result = $externalCallService->finishForUserId(
        $callId,
        $b24UserId,
        $duration,
        $money,
        $telephonyCallStatusCode,
        $isAddCallToChat,
        $failedReason,
        $userVote
    );

    // Process and print the result
    $itemResult = $result->getExternalCallFinished();
    print($itemResult->CALL_ID);
    print($itemResult->EXTERNAL_CALL_ID);
    print($itemResult->PORTAL_USER_ID);
    print($itemResult->PHONE_NUMBER);
    print($itemResult->PORTAL_NUMBER);
    print($itemResult->INCOMING);
    print($itemResult->CALL_DURATION);
    print($itemResult->CALL_START_DATE);
    print($itemResult->CALL_STATUS);
    print($itemResult->CALL_VOTE);
    print($itemResult->COST);
    print($itemResult->COST_CURRENCY);
    print($itemResult->CALL_FAILED_CODE);
    print($itemResult->CALL_FAILED_REASON);
    print($itemResult->REST_APP_ID);
    print($itemResult->REST_APP_NAME);
    print($itemResult->CRM_ACTIVITY_ID);
    print($itemResult->COMMENT);
    print($itemResult->CRM_ENTITY_TYPE);
    print($itemResult->CRM_ENTITY_ID);
    print($itemResult->ID);
} catch (Throwable $e) {
    // Handle the exception
    print('Error: ' . $e->getMessage());
}

//generated_example_code_finish