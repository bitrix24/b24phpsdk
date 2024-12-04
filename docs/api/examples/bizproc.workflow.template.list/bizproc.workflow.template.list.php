<?php
declare(strict_types=1);

use Bitrix24\SDK\Tests\Integration\Fabric;

require_once 'tests/bootstrap.php';
$serviceBuilder = Fabric::getServiceBuilder(true);

//generated_example_code_start

try {
    $result = $serviceBuilder
        ->getBizProcScope()
        ->template()
        ->list(
            ['ID', 'MODULE_ID', 'ENTITY', 'DOCUMENT_TYPE', 'AUTO_EXECUTE', 'NAME', 'TEMPLATE', 'PARAMETERS', 'VARIABLES', 'CONSTANTS', 'MODIFIED', 'IS_MODIFIED', 'USER_ID', 'SYSTEM_CODE'],
            []
        );

    foreach ($result->getTemplates() as $template) {
        print("ID: " . $template->ID . "\n");
        print("MODULE_ID: " . $template->MODULE_ID . "\n");
        print("ENTITY: " . $template->ENTITY . "\n");
        print("DOCUMENT_TYPE: " . json_encode($template->DOCUMENT_TYPE) . "\n");
        print("AUTO_EXECUTE: " . ($template->AUTO_EXECUTE ? $template->AUTO_EXECUTE->value : 'null') . "\n");
        print("NAME: " . $template->NAME . "\n");
        print("TEMPLATE: " . json_encode($template->TEMPLATE) . "\n");
        print("PARAMETERS: " . json_encode($template->PARAMETERS) . "\n");
        print("VARIABLES: " . json_encode($template->VARIABLES) . "\n");
        print("CONSTANTS: " . json_encode($template->CONSTANTS) . "\n");
        print("MODIFIED: " . ($template->MODIFIED ? $template->MODIFIED->format(DATE_ATOM) : 'null') . "\n");
        print("IS_MODIFIED: " . ($template->IS_MODIFIED ? 'true' : 'false') . "\n");
        print("USER_ID: " . $template->USER_ID . "\n");
        print("SYSTEM_CODE: " . $template->SYSTEM_CODE . "\n");
        print("\n");
    }
} catch (Throwable $e) {
    print("Error: " . $e->getMessage() . "\n");
}

//generated_example_code_finish