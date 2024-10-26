<?php
declare(strict_types=1);

use Bitrix24\SDK\Tests\Integration\Fabric;

require_once 'tests/bootstrap.php';
$serviceBuilder = Fabric::getServiceBuilder(true);

//generated_example_code_start

```php
try {
    $result = $serviceBuilder->getBizProcScope()
        ->workflow()
        ->instances(
            ['ID', 'MODIFIED', 'OWNED_UNTIL', 'MODULE_ID', 'ENTITY', 'DOCUMENT_ID', 'STARTED', 'STARTED_BY', 'TEMPLATE_ID'],
            ['STARTED' => 'DESC'],
            []
        );

    foreach ($result->getInstances() as $instance) {
        print("ID: {$instance->ID}\n");
        print("Modified: {$instance->MODIFIED->format(DATE_ATOM)}\n");
        print("Owned Until: " . ($instance->OWNED_UNTIL ? $instance->OWNED_UNTIL->format(DATE_ATOM) : 'null') . "\n");
        print("Started: " . ($instance->STARTED ? $instance->STARTED->format(DATE_ATOM) : 'null') . "\n");
        print("Module ID: " . ($instance->MODULE_ID ?? 'null') . "\n");
        print("Entity: " . ($instance->ENTITY ?? 'null') . "\n");
        print("Document ID: " . ($instance->DOCUMENT_ID ?? 'null') . "\n");
        print("Started By: " . ($instance->STARTED_BY ?? 'null') . "\n");
        print("Template ID: " . ($instance->TEMPLATE_ID ?? 'null') . "\n");
    }
} catch (Throwable $e) {
    print("Error: " . $e->getMessage() . "\n");
}
```

//generated_example_code_finish