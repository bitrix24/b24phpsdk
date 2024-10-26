<?php
declare(strict_types=1);

use Bitrix24\SDK\Tests\Integration\Fabric;

require_once 'tests/bootstrap.php';
$serviceBuilder = Fabric::getServiceBuilder(true);

//generated_example_code_start

try {
    $result = $serviceBuilder
        ->getTelephonyScope()
        ->getVoximplantScope()
        ->getTTScope()
        ->getVoices()
        ->get();

    foreach ($result->getVoices() as $voice) {
        print("Code: " . $voice->CODE . ", Name: " . $voice->NAME . PHP_EOL);
    }
} catch (Throwable $e) {
    print("Error: " . $e->getMessage());
}

//generated_example_code_finish