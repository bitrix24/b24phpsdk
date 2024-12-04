<?php
declare(strict_types=1);

use Bitrix24\SDK\Tests\Integration\Fabric;

require_once 'tests/bootstrap.php';
$serviceBuilder = Fabric::getServiceBuilder(true);

//generated_example_code_start

try {
    $dealId = 123; // Replace with your actual deal ID
    $contactId = 456; // Replace with your actual contact ID
    $isPrimary = true; // Set to true or false based on your requirement
    $sort = 100; // Set the sort order

    $result = $serviceBuilder
        ->getCRMScope()
        ->dealContact()
        ->add($dealId, $contactId, $isPrimary, $sort);

    print($result->getId());
} catch (Throwable $e) {
    print('Error: ' . $e->getMessage());
}

//generated_example_code_finish