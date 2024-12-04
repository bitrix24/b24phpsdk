<?php
declare(strict_types=1);

use Bitrix24\SDK\Tests\Integration\Fabric;

require_once 'tests/bootstrap.php';
$serviceBuilder = Fabric::getServiceBuilder(true);

//generated_example_code_start

try {
    $order = [
        // Example order fields
        'SORT' => 'ASC',
        'FIELD_NAME' => 'CustomFieldName',
    ];

    $filter = [
        // Example filter fields
        'ENTITY_ID' => 'DEAL',
        'USER_TYPE_ID' => 'string',
    ];

    $result = $serviceBuilder
        ->getCRMScope()
        ->dealUserfield()
        ->list($order, $filter);

    foreach ($result->getUserfields() as $item) {
        print($item->ID);
        print($item->ENTITY_ID);
        print($item->FIELD_NAME);
        print($item->USER_TYPE_ID);
        print($item->XML_ID);
        print($item->SORT);
        print($item->MULTIPLE);
        print($item->MANDATORY);
        print($item->SHOW_FILTER);
        print($item->SHOW_IN_LIST);
        print($item->EDIT_IN_LIST);
        print($item->IS_SEARCHABLE);
        print($item->EDIT_FORM_LABEL);
        print($item->LIST_COLUMN_LABEL);
        print($item->LIST_FILTER_LABEL);
        print($item->ERROR_MESSAGE);
        print($item->HELP_MESSAGE);
        print($item->LIST);
        print($item->SETTINGS);
    }
} catch (Throwable $e) {
    // Handle exception
    print('Error: ' . $e->getMessage());
}

//generated_example_code_finish