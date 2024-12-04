<?php
declare(strict_types=1);

use Bitrix24\SDK\Tests\Integration\Fabric;

require_once 'tests/bootstrap.php';
$serviceBuilder = Fabric::getServiceBuilder(true);

//generated_example_code_start

try {
    $contactId = 123; // Example contact ID
    $contactResult = $serviceBuilder
        ->getCRMScope()
        ->contact()
        ->get($contactId);

    $itemResult = $contactResult->contact();

    print("ID: " . $itemResult->ID . PHP_EOL);
    print("Name: " . $itemResult->NAME . PHP_EOL);
    print("Last Name: " . $itemResult->LAST_NAME . PHP_EOL);
    print("Birthday: " . $itemResult->BIRTHDATE?->format(DATE_ATOM) . PHP_EOL);
    print("Created Date: " . $itemResult->DATE_CREATE->format(DATE_ATOM) . PHP_EOL);
    print("Modified Date: " . $itemResult->DATE_MODIFY->format(DATE_ATOM) . PHP_EOL);
} catch (Throwable $e) {
    print("Error: " . $e->getMessage() . PHP_EOL);
}

//generated_example_code_finish