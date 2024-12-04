<?php
declare(strict_types=1);

use Bitrix24\SDK\Tests\Integration\Fabric;

require_once 'tests/bootstrap.php';
$serviceBuilder = Fabric::getServiceBuilder(true);

//generated_example_code_start

try {
    $result = $serviceBuilder
        ->getTelephonyScope()
        ->externalLine()
        ->add('8-9938-832799312', true, 'My External Line');

    $itemResult = $result->getExternalLineAddResultItem();
    print("ID: " . $itemResult->ID);
} catch (Throwable $e) {
    print("Error: " . $e->getMessage());
}

//generated_example_code_finish