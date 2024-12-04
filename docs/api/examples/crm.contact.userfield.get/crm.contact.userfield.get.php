<?php
declare(strict_types=1);

use Bitrix24\SDK\Tests\Integration\Fabric;

require_once 'tests/bootstrap.php';
$serviceBuilder = Fabric::getServiceBuilder(true);

//generated_example_code_start

try {
    $contactUserfieldItemId = 123; // Example ID
    $result = $serviceBuilder
        ->getCRMScope()
        ->contactUserfield()
        ->get($contactUserfieldItemId);

    $itemResult = $result->userfieldItem();
    
    print($itemResult->ID);
    print($itemResult->ENTITY_ID);
    print($itemResult->FIELD_NAME);
    print($itemResult->USER_TYPE_ID);
    print($itemResult->XML_ID);
    print($itemResult->SORT);
    print($itemResult->MULTIPLE);
    print($itemResult->MANDATORY);
    print($itemResult->SHOW_FILTER);
    print($itemResult->SHOW_IN_LIST);
    print($itemResult->EDIT_IN_LIST);
    print($itemResult->IS_SEARCHABLE);
    print($itemResult->EDIT_FORM_LABEL);
    print($itemResult->LIST_COLUMN_LABEL);
    print($itemResult->LIST_FILTER_LABEL);
    print($itemResult->ERROR_MESSAGE);
    print($itemResult->HELP_MESSAGE);
    print($itemResult->LIST);
    print($itemResult->SETTINGS);
} catch (Throwable $e) {
    // Handle the exception
    print("Error: " . $e->getMessage());
}

//generated_example_code_finish