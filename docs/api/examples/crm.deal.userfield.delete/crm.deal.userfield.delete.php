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
        ->dealUserfield()
        ->delete($userfieldId);

    if ($result->isSuccess()) {
        print("Userfield deleted successfully.");
    } else {
        print("Failed to delete userfield.");
    }
} catch (Throwable $e) {
    print("An error occurred: " . $e->getMessage());
}

//generated_example_code_finish