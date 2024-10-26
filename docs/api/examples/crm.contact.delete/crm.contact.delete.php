<?php
declare(strict_types=1);

use Bitrix24\SDK\Tests\Integration\Fabric;

require_once 'tests/bootstrap.php';
$serviceBuilder = Fabric::getServiceBuilder(true);

//generated_example_code_start

try {
    $contactId = 123; // Example contact ID
    $result = $serviceBuilder
        ->getCRMScope()
        ->contact()
        ->delete($contactId);
    
    if ($result->isSuccess()) {
        print("Contact with ID {$contactId} has been deleted successfully.");
    } else {
        print("Failed to delete contact: " . json_encode($result->getErrors()));
    }
} catch (Throwable $e) {
    print("An error occurred: " . $e->getMessage());
}

//generated_example_code_finish