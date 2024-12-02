<?php
declare(strict_types=1);

use Bitrix24\SDK\Tests\Integration\Fabric;

require_once 'tests/bootstrap.php';
$serviceBuilder = Fabric::getServiceBuilder(true);

//generated_example_code_start

```php
try {
    $templateId = 123; // Example template ID
    $workflowDocumentType = null; // Example WorkflowDocumentType, replace with actual instance if needed
    $name = "Updated Template Name"; // Example name
    $description = "Updated Template Description"; // Example description
    $workflowAutoExecutionType = null; // Example WorkflowAutoExecutionType, replace with actual instance if needed
    $filename = null; // Example filename, replace with actual filename if needed

    $result = $serviceBuilder
        ->getBizProcScope()
        ->template()
        ->update(
            $templateId,
            $workflowDocumentType,
            $name,
            $description,
            $workflowAutoExecutionType,
            $filename
        );

    if ($result->isSuccess()) {
        print($result->getCoreResponse()->getResponseData()->getResult()[0]);
    } else {
        print("Update failed");
    }
} catch (Throwable $e) {
    print("An error occurred: " . $e->getMessage());
}
```

//generated_example_code_finish