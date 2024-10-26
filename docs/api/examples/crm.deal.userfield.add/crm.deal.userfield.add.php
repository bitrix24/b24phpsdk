<?php
declare(strict_types=1);

use Bitrix24\SDK\Tests\Integration\Fabric;

require_once 'tests/bootstrap.php';
$serviceBuilder = Fabric::getServiceBuilder(true);

//generated_example_code_start

try {
    $userfieldItemFields = [
        'FIELD_NAME' => 'Test Field',
        'USER_TYPE_ID' => 'string',
        'XML_ID' => 'test_field_1',
        'SORT' => '100',
        'MULTIPLE' => 'N',
        'MANDATORY' => 'N',
        'SHOW_FILTER' => 'Y',
        'SHOW_IN_LIST' => 'Y',
        'EDIT_IN_LIST' => 'Y',
        'IS_SEARCHABLE' => 'Y',
        'EDIT_FORM_LABEL' => 'Test Field Label',
        'LIST_COLUMN_LABEL' => 'Test Field List Label',
        'LIST_FILTER_LABEL' => 'Test Field Filter Label',
        'ERROR_MESSAGE' => 'Error occurred',
        'HELP_MESSAGE' => 'Help message for Test Field',
        'LIST' => '',
        'SETTINGS' => '',
    ];

    $result = $serviceBuilder
        ->getCRMScope()
        ->dealUserfield()
        ->add($userfieldItemFields);

    print($result->getId());
} catch (Throwable $e) {
    print('Error: ' . $e->getMessage());
}

//generated_example_code_finish