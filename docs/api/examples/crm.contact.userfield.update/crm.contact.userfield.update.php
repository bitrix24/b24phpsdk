<?php
declare(strict_types=1);

use Bitrix24\SDK\Tests\Integration\Fabric;

require_once 'tests/bootstrap.php';
$serviceBuilder = Fabric::getServiceBuilder(true);

//generated_example_code_start

try {
    $contactUserfieldItemId = 123; // Example ID
    $userfieldFieldsToUpdate = [
        'FIELD_NAME' => 'New Field Name',
        'USER_TYPE_ID' => 'string',
        'SORT' => '100',
        'MULTIPLE' => 'N',
        'MANDATORY' => 'N',
        'SHOW_FILTER' => 'Y',
        'SHOW_IN_LIST' => 'Y',
        'EDIT_IN_LIST' => 'Y',
        'IS_SEARCHABLE' => 'Y',
        'EDIT_FORM_LABEL' => 'New Label',
        'LIST_COLUMN_LABEL' => 'Column Label',
        'LIST_FILTER_LABEL' => 'Filter Label',
        'ERROR_MESSAGE' => 'Error Message',
        'HELP_MESSAGE' => 'Help Message',
        'LIST' => '',
        'SETTINGS' => '',
    ];

    $result = $serviceBuilder
        ->getCRMScope()
        ->contactUserfield()
        ->update($contactUserfieldItemId, $userfieldFieldsToUpdate);

    if ($result->isSuccess()) {
        print($result->getCoreResponse()->getResponseData()->getResult()[0]);
    } else {
        print("Update failed.");
    }
} catch (Throwable $e) {
    print("Error: " . $e->getMessage());
}

//generated_example_code_finish