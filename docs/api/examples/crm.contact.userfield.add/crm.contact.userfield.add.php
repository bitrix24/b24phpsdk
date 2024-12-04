<?php
declare(strict_types=1);

use Bitrix24\SDK\Tests\Integration\Fabric;

require_once 'tests/bootstrap.php';
$serviceBuilder = Fabric::getServiceBuilder(true);

//generated_example_code_start

try {
    $userfieldItemFields = [
        'FIELD_NAME' => 'UF_CRM_example',
        'USER_TYPE_ID' => 'string',
        'XML_ID' => 'xml_example',
        'SORT' => '100',
        'MULTIPLE' => 'N',
        'MANDATORY' => 'Y',
        'SHOW_FILTER' => 'Y',
        'SHOW_IN_LIST' => 'Y',
        'EDIT_IN_LIST' => 'Y',
        'IS_SEARCHABLE' => 'Y',
        'EDIT_FORM_LABEL' => 'Example Field',
        'LIST_COLUMN_LABEL' => 'Example Column',
        'LIST_FILTER_LABEL' => 'Example Filter',
        'ERROR_MESSAGE' => 'Error occurred',
        'HELP_MESSAGE' => 'Help message',
        'LIST' => 'list_value',
        'SETTINGS' => 'settings_value',
    ];

    $result = $serviceBuilder
        ->getCRMScope()
        ->contactUserfield()
        ->add($userfieldItemFields);

    print($result->getId());
} catch (Throwable $e) {
    print('Error: ' . $e->getMessage());
}

//generated_example_code_finish