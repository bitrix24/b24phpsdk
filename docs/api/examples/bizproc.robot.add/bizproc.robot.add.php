<?php
declare(strict_types=1);

use Bitrix24\SDK\Tests\Integration\Fabric;

require_once 'tests/bootstrap.php';
$serviceBuilder = Fabric::getServiceBuilder(true);

//generated_example_code_start

try {
    $result = $serviceBuilder
        ->getBizProcScope()
        ->robot()
        ->add(
            'robot_code', // string $code
            'https://example.com/handler', // string $handlerUrl
            1, // int $b24AuthUserId
            ['en' => 'Robot Name'], // array $localizedRobotName
            true, // bool $isUseSubscription
            [], // array $properties
            false, // bool $isUsePlacement
            [] // array $returnProperties
        );

    if ($result->isSuccess()) {
        print_r($result->getCoreResponse()->getResponseData()->getResult());
    } else {
        print("Failed to add robot.");
    }
} catch (Throwable $e) {
    print("Error: " . $e->getMessage());
}

//generated_example_code_finish