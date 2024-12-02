<?php
declare(strict_types=1);

use Bitrix24\SDK\Tests\Integration\Fabric;

require_once 'tests/bootstrap.php';
$serviceBuilder = Fabric::getServiceBuilder(true);

//generated_example_code_start

```php
try {
    $taskId = 123; // example task ID
    $status = new Bitrix24\SDK\Services\Workflows\Common\WorkflowTaskCompleteStatusType('completed'); // example status
    $comment = 'Task completed successfully'; // example comment
    $taskFields = null; // or an associative array if needed

    $result = $serviceBuilder
        ->getBizProcScope()
        ->task()
        ->complete($taskId, $status, $comment, $taskFields);

    if ($result->isSuccess()) {
        print_r($result->getCoreResponse()->getResponseData()->getResult());
    } else {
        print("Failed to complete the task.");
    }
} catch (Throwable $e) {
    print("An error occurred: " . $e->getMessage());
}
```

//generated_example_code_finish