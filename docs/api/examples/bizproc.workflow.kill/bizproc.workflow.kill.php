<?php
declare(strict_types=1);

use Bitrix24\SDK\Tests\Integration\Fabric;

require_once 'tests/bootstrap.php';
$serviceBuilder = Fabric::getServiceBuilder(true);

//generated_example_code_start

try {
    $workflowId = 'your_workflow_id'; // Replace with your actual workflow ID
    $result = $serviceBuilder->getBizProcScope()
        ->workflow()
        ->kill($workflowId);

    if ($result->isSuccess()) {
        print_r($result->getCoreResponse()->getResponseData()->getResult());
    } else {
        print('Failed to kill workflow: ' . json_encode($result->getCoreResponse()->getResponseData()->getResult()));
    }
} catch (Throwable $e) {
    print('Error occurred: ' . $e->getMessage());
}

//generated_example_code_finish