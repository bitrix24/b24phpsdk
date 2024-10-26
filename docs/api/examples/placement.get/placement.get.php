<?php
declare(strict_types=1);

use Bitrix24\SDK\Tests\Integration\Fabric;

require_once 'tests/bootstrap.php';
$serviceBuilder = Fabric::getServiceBuilder(true);

//generated_example_code_start

try {
    $result = $serviceBuilder->getPlacementScope()->get();
    $placements = $result->getPlacementsLocationInformation();
    
    foreach ($placements as $item) {
        print("Placement: " . $item->placement . "\n");
        print("Handler: " . $item->handler . "\n");
        print("Title: " . $item->title . "\n");
        print("Description: " . $item->description . "\n");
        print("Options: " . json_encode($item->options) . "\n");
        print("Language All: " . json_encode($item->langAll) . "\n");
    }
} catch (Throwable $e) {
    print("Error: " . $e->getMessage());
}

//generated_example_code_finish