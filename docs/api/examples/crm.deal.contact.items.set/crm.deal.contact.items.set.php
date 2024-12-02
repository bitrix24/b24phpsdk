<?php
declare(strict_types=1);

use Bitrix24\SDK\Tests\Integration\Fabric;

require_once 'tests/bootstrap.php';
$serviceBuilder = Fabric::getServiceBuilder(true);

//generated_example_code_start

try {
    $dealId = 123; // Example deal ID
    $contactItems = [
        [
            'CONTACT_ID' => 456, // Example contact ID
            'SORT' => 100,
            'IS_PRIMARY' => 'Y',
        ],
        [
            'CONTACT_ID' => 789, // Example contact ID
            'SORT' => 200,
            'IS_PRIMARY' => 'N',
        ],
    ];

    $result = $serviceBuilder
        ->getCRMScope()
        ->dealContact()
        ->itemsSet($dealId, $contactItems);

    if ($result->isSuccess()) {
        print_r($result->getCoreResponse()->getResponseData()->getResult());
    } else {
        print("Failed to set contact items for deal: " . $result->getError()->getMessage());
    }
} catch (Throwable $e) {
    print("An error occurred: " . $e->getMessage());
}

//generated_example_code_finish