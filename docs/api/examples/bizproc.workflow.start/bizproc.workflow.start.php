<?php
declare(strict_types=1);

use Bitrix24\SDK\Tests\Integration\Fabric;

require_once 'tests/bootstrap.php';
$serviceBuilder = Fabric::getServiceBuilder(true);

//generated_example_code_start

```php
try {
    $workflowDocumentType = new Bitrix24\SDK\Services\Workflows\Common\DocumentType('crmLead'); // Example DocumentType
    $bizProcTemplateId = 123; // Example Template ID
    $entityId = 456; // Example Entity ID
    $callParameters = []; // Example Call Parameters
    $smartProcessId = null; // Example Smart Process ID

    $result = $serviceBuilder
        ->getBizProcScope()
        ->workflow()
        ->start($workflowDocumentType, $bizProcTemplateId, $entityId, $callParameters, $smartProcessId);

    // Process return result
    print($result->getRunningWorkflowInstanceId());
} catch (Throwable $e) {
    print("Error: " . $e->getMessage());
}
```

//generated_example_code_finish