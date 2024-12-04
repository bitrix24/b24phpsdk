<?php
declare(strict_types=1);

use Bitrix24\SDK\Tests\Integration\Fabric;

require_once 'tests/bootstrap.php';
$serviceBuilder = Fabric::getServiceBuilder(true);

//generated_example_code_start

try {
    $emails = ['example@example.com'];
    $entityType = null; // or set to a specific EntityType if needed

    $duplicateResult = $serviceBuilder
        ->getCRMScope()
        ->duplicate()
        ->findByEmail($emails, $entityType);

    if ($duplicateResult->hasDuplicateContacts()) {
        $contactsId = $duplicateResult->getContactsId();
        print_r($contactsId);
    } else {
        print("No duplicates found.");
    }
} catch (\Throwable $e) {
    print("An error occurred: " . $e->getMessage());
}

//generated_example_code_finish