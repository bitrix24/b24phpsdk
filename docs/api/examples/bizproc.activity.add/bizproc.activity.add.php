<?php
declare(strict_types=1);

use Bitrix24\SDK\Tests\Integration\Fabric;

require_once 'tests/bootstrap.php';
$serviceBuilder = Fabric::getServiceBuilder(true);

//generated_example_code_start

try {
    $result = $serviceBuilder
        ->getBizProcScope()
        ->activity()
        ->add(
            'unique_activity_code', // string $code
            'https://example.com/handler', // string $handlerUrl
            1, // int $b24AuthUserId
            ['en' => 'Activity Name'], // array $localizedName
            ['en' => 'Activity Description'], // array $localizedDescription
            true, // bool $isUseSubscription
            [], // array $properties
            false, // bool $isUsePlacement
            [], // array $returnProperties
            new Bitrix24\SDK\Services\Workflows\Common\WorkflowDocumentType(), // Bitrix24\SDK\Services\Workflows\Common\WorkflowDocumentType $documentType
            [] // array $limitationFilter
        );

    if ($result->isSuccess()) {
        print($result->getCoreResponse()->getResponseData()->getResult()[0]);
    } else {
        print('Failed to add activity.');
    }
} catch (Throwable $e) {
    print('Error: ' . $e->getMessage());
}

//generated_example_code_finish