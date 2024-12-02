<?php
declare(strict_types=1);

use Bitrix24\SDK\Tests\Integration\Fabric;

require_once 'tests/bootstrap.php';
$serviceBuilder = Fabric::getServiceBuilder(true);

//generated_example_code_start

try {
    $dealId = 123; // Replace with the actual deal ID you want to delete contacts from
    $result = $serviceBuilder->getCRMScope()->dealContact()->itemsDelete($dealId);
    
    if ($result->isSuccess()) {
        print("Successfully deleted contacts from deal ID: $dealId");
    } else {
        print("Failed to delete contacts. Result: " . json_encode($result));
    }
} catch (Throwable $e) {
    print("An error occurred: " . $e->getMessage());
}

//generated_example_code_finish