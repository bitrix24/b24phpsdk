<?php
declare(strict_types=1);

use Bitrix24\SDK\Tests\Integration\Fabric;

require_once 'tests/bootstrap.php';
$serviceBuilder = Fabric::getServiceBuilder(true);

//generated_example_code_start

```php
try {
    $workflowDocumentType = new Bitrix24\SDK\Services\Workflows\Common\WorkflowDocumentType(/* parameters */);
    $name = "Sample Workflow Template";
    $description = "This is a sample workflow template description.";
    $workflowAutoExecutionType = new Bitrix24\SDK\Services\Workflows\Common\WorkflowAutoExecutionType(/* parameters */);
    $filename = "template_file.bpmn";

    $result = $serviceBuilder
        ->getBizProcScope()
        ->template()
        ->add($workflowDocumentType, $name, $description, $workflowAutoExecutionType, $filename);

    print($result->getId());
} catch (Throwable $e) {
    // Handle exception
    print("Error: " . $e->getMessage());
}
```

//generated_example_code_finish