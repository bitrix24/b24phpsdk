<?php
declare(strict_types=1);

use Bitrix24\SDK\Tests\Integration\Fabric;

require_once 'tests/bootstrap.php';
$serviceBuilder = Fabric::getServiceBuilder(true);

//generated_example_code_start

try {
    $templateId = 123; // Replace with the actual template ID you want to delete
    $result = $serviceBuilder
        ->getBizProcScope()
        ->template()
        ->delete($templateId);

    if ($result->isSuccess()) {
        print("Template with ID {$templateId} deleted successfully.\n");
    } else {
        print("Failed to delete template with ID {$templateId}.\n");
    }
} catch (\Throwable $e) {
    print("An error occurred: " . $e->getMessage() . "\n");
}

//generated_example_code_finish