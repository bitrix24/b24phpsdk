<?php
declare(strict_types=1);

use Bitrix24\SDK\Tests\Integration\Fabric;

require_once 'tests/bootstrap.php';
$serviceBuilder = Fabric::getServiceBuilder(true);

//generated_example_code_start

try {
    $order = ['ID' => 'DESC'];
    $filter = [
        'ID' => 1,
        'WORKFLOW_ID' => 'workflow_123',
        'DOCUMENT_NAME' => 'Document Name',
        'DESCRIPTION' => 'Task Description',
        'NAME' => 'Task Name',
        'MODIFIED' => Carbon\CarbonImmutable::now()->format(DATE_ATOM),
        'WORKFLOW_STARTED' => Carbon\CarbonImmutable::now()->format(DATE_ATOM),
        'WORKFLOW_STARTED_BY' => 1,
        'OVERDUE_DATE' => Carbon\CarbonImmutable::now()->addDays(5)->format(DATE_ATOM),
        'WORKFLOW_TEMPLATE_ID' => 2,
        'WORKFLOW_TEMPLATE_NAME' => 'Template Name',
        'WORKFLOW_STATE' => 'In Progress',
        'STATUS' => Bitrix24\SDK\Services\Workflows\Common\WorkflowTaskStatusType::from(1),
        'USER_ID' => 1,
        'USER_STATUS' => Bitrix24\SDK\Services\Workflows\Common\WorkflowTaskUserStatusType::from(1),
        'MODULE_ID' => 'module_1',
        'ENTITY' => Bitrix24\SDK\Services\Workflows\Common\DocumentType::from('document_type'),
        'DOCUMENT_ID' => 123,
        'ACTIVITY' => Bitrix24\SDK\Services\Workflows\Common\WorkflowTaskActivityType::from(1),
        'PARAMETERS' => [],
        'DOCUMENT_URL' => 'https://example.com/document/123'
    ];
    $select = ['ID', 'WORKFLOW_ID', 'DOCUMENT_NAME', 'NAME', 'DESCRIPTION', 'MODIFIED', 'WORKFLOW_STARTED', 'WORKFLOW_STARTED_BY', 'OVERDUE_DATE', 'WORKFLOW_TEMPLATE_ID', 'WORKFLOW_TEMPLATE_NAME', 'WORKFLOW_STATE', 'STATUS', 'USER_ID', 'USER_STATUS', 'MODULE_ID', 'ENTITY', 'DOCUMENT_ID', 'ACTIVITY', 'PARAMETERS', 'DOCUMENT_URL'];

    $result = $serviceBuilder->getBizProcScope()->getWorkflowsScope()->getTask()->list($order, $filter, $select);
    $tasks = $result->getTasks();

    foreach ($tasks as $task) {
        print("ID: {$task->ID}\n");
        print("Workflow ID: {$task->WORKFLOW_ID}\n");
        print("Document Name: {$task->DOCUMENT_NAME}\n");
        print("Name: {$task->NAME}\n");
        print("Description: {$task->DESCRIPTION}\n");
        print("Modified: {$task->MODIFIED}\n");
        print("Workflow Started: {$task->WORKFLOW_STARTED}\n");
        print("Started By: {$task->WORKFLOW_STARTED_BY}\n");
        print("Overdue Date: {$task->OVERDUE_DATE}\n");
        print("Workflow Template ID: {$task->WORKFLOW_TEMPLATE_ID}\n");
        print("Workflow Template Name: {$task->WORKFLOW_TEMPLATE_NAME}\n");
        print("Workflow State: {$task->WORKFLOW_STATE}\n");
        print("Status: {$task->STATUS}\n");
        print("User ID: {$task->USER_ID}\n");
        print("User Status: {$task->USER_STATUS}\n");
        print("Module ID: {$task->MODULE_ID}\n");
        print("Entity: {$task->ENTITY}\n");
        print("Document ID: {$task->DOCUMENT_ID}\n");
        print("Activity: {$task->ACTIVITY}\n");
        print("Parameters: " . json_encode($task->PARAMETERS) . "\n");
        print("Document URL: {$task->DOCUMENT_URL}\n");
    }
} catch (Throwable $e) {
    print("Error: " . $e->getMessage());
}

//generated_example_code_finish