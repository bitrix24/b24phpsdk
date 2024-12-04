<?php
declare(strict_types=1);

use Bitrix24\SDK\Tests\Integration\Fabric;

require_once 'tests/bootstrap.php';
$serviceBuilder = Fabric::getServiceBuilder(true);

//generated_example_code_start

try {
    $id = 123; // Example lead ID to delete
    $result = $serviceBuilder
        ->getCRMScope()
        ->lead()
        ->delete($id);

    if ($result->isSuccess()) {
        print("Lead with ID $id has been successfully deleted.");
    } else {
        print("Failed to delete lead with ID $id.");
    }
} catch (Throwable $e) {
    print("An error occurred: " . $e->getMessage());
}

//generated_example_code_finish