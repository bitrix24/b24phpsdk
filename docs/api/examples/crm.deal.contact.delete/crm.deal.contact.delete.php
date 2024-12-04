<?php
declare(strict_types=1);

use Bitrix24\SDK\Tests\Integration\Fabric;

require_once 'tests/bootstrap.php';
$serviceBuilder = Fabric::getServiceBuilder(true);

//generated_example_code_start

try {
    $dealId = 123; // Example deal ID
    $contactId = 456; // Example contact ID

    $result = $serviceBuilder
        ->getCRMScope()
        ->dealContact()
        ->delete($dealId, $contactId);

    if ($result->isSuccess()) {
        print("Item deleted successfully.");
    } else {
        print("Failed to delete item.");
    }
} catch (\Throwable $e) {
    print("An error occurred: " . $e->getMessage());
}

//generated_example_code_finish