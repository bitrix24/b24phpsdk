<?php
declare(strict_types=1);

use Bitrix24\SDK\Tests\Integration\Fabric;

require_once 'tests/bootstrap.php';
$serviceBuilder = Fabric::getServiceBuilder(true);

//generated_example_code_start

try {
    $result = $serviceBuilder
        ->getTelephonyScope()
        ->sip()
        ->get();
    
    $lines = $result->getLines();
    foreach ($lines as $line) {
        print("ID: {$line->ID}\n");
        print("Type: {$line->TYPE}\n");
        print("Config ID: {$line->CONFIG_ID}\n");
        print("Reg ID: {$line->REG_ID}\n");
        print("Server: {$line->SERVER}\n");
        print("Login: {$line->LOGIN}\n");
        print("Title: {$line->TITLE}\n");
        print("\n");
    }
} catch (Throwable $e) {
    print("Error: {$e->getMessage()}\n");
}

//generated_example_code_finish