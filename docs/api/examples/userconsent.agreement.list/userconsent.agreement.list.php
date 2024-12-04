<?php
declare(strict_types=1);

use Bitrix24\SDK\Tests\Integration\Fabric;

require_once 'tests/bootstrap.php';
$serviceBuilder = Fabric::getServiceBuilder(true);

//generated_example_code_start

try {
    $userConsentAgreementsResult = $serviceBuilder
        ->getUserConsentScope()
        ->userConsentAgreement()
        ->list();

    foreach ($userConsentAgreementsResult->getAgreements() as $agreement) {
        print("ID: " . $agreement->ID . "\n");
        print("Name: " . $agreement->NAME . "\n");
        print("Language ID: " . $agreement->LANGUAGE_ID . "\n");
        print("Active: " . ($agreement->ACTIVE ? 'Yes' : 'No') . "\n");
    }
} catch (Throwable $e) {
    print("An error occurred: " . $e->getMessage());
}

//generated_example_code_finish