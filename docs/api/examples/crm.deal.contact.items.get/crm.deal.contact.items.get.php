<?php
declare(strict_types=1);

use Bitrix24\SDK\Tests\Integration\Fabric;

require_once 'tests/bootstrap.php';
$serviceBuilder = Fabric::getServiceBuilder(true);

//generated_example_code_start

try {
    $dealId = 123; // Replace with the actual deal ID
    $result = $serviceBuilder
        ->getCRMScope()
        ->dealContact()
        ->itemsGet($dealId);
    
    foreach ($result->getDealContacts() as $item) {
        print("CONTACT_ID: " . $item->CONTACT_ID . "\n");
        print("SORT: " . $item->SORT . "\n");
        print("ROLE_ID: " . $item->ROLE_ID . "\n");
        print("IS_PRIMARY: " . $item->IS_PRIMARY . "\n");
    }
} catch (Throwable $e) {
    print("Error: " . $e->getMessage());
}

//generated_example_code_finish