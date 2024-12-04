<?php
declare(strict_types=1);

use Bitrix24\SDK\Tests\Integration\Fabric;

require_once 'tests/bootstrap.php';
$serviceBuilder = Fabric::getServiceBuilder(true);

//generated_example_code_start

try {
    $userfieldId = 123; // Replace with the actual userfield ID you want to delete
    $result = $serviceBuilder
        ->getCRMScope()
        ->contactUserfield()
        ->delete($userfieldId);

    if ($result->isSuccess()) {
        print("Deleted item successfully.");
    } else {
        print("Failed to delete item.");
    }
} catch (Throwable $e) {
    print("An error occurred: " . $e->getMessage());
}

//generated_example_code_finish