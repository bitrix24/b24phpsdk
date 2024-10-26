<?php
declare(strict_types=1);

use Bitrix24\SDK\Tests\Integration\Fabric;

require_once 'tests/bootstrap.php';
$serviceBuilder = Fabric::getServiceBuilder(true);

//generated_example_code_start

try {
    $consentFields = [
        // Populate the consent fields as per your application requirements
        'ID' => 1,
        'NAME' => 'User Consent',
        'DATE_CREATE' => (new DateTime())->format(DateTime::ATOM),
        'DATE_UPDATE' => (new DateTime())->format(DateTime::ATOM),
        // Add other necessary fields here
    ];

    $result = $serviceBuilder->getUserConsentScope()
                             ->add($consentFields);
    
    print($result->getId());
} catch (Throwable $e) {
    // Handle the exception
    print('Error: ' . $e->getMessage());
}

//generated_example_code_finish