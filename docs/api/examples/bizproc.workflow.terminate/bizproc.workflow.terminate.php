<?php
declare(strict_types=1);

use Bitrix24\SDK\Tests\Integration\Fabric;

require_once 'tests/bootstrap.php';
$serviceBuilder = Fabric::getServiceBuilder(true);

//generated_example_code_start

try {
    $workflowId = 'your_workflow_id'; // Replace with actual workflow ID
    $message = 'Workflow terminated'; // Replace with actual message

    $result = $serviceBuilder
        ->getBizProcScope()
        ->workflow()
        ->terminate($workflowId, $message);

    if ($result->isSuccess()) {
        print($result->getCoreResponse()->getResponseData()->getResult()[0]);
    } else {
        print('Termination failed.');
    }
} catch (Throwable $e) {
    print('Error: ' . $e->getMessage());
}

//generated_example_code_finish