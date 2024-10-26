<?php
declare(strict_types=1);

use Bitrix24\SDK\Tests\Integration\Fabric;

require_once 'tests/bootstrap.php';
$serviceBuilder = Fabric::getServiceBuilder(true);

//generated_example_code_start

try {
    $placementCode = 'example_placement_code';
    $handlerUrl = 'https://example.com/handler';
    $lang = ['en' => 'English', 'ru' => 'Русский'];

    $result = $serviceBuilder
        ->getPlacementScope()
        ->bind($placementCode, $handlerUrl, $lang);

    if ($result->isSuccess()) {
        print($result->getCoreResponse()->getResponseData()->getResult()[0]);
    } else {
        print('Failed to bind placement.');
    }
} catch (Throwable $e) {
    print('Error: ' . $e->getMessage());
}

//generated_example_code_finish