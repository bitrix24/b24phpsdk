<?php
declare(strict_types=1);

use Bitrix24\SDK\Tests\Integration\Fabric;

require_once 'tests/bootstrap.php';
$serviceBuilder = Fabric::getServiceBuilder(true);

//generated_example_code_start

try {
    $userfieldItemId = 123; // Example user field item ID
    $userfieldFieldsToUpdate = [
        'FIELD_NAME' => 'New Field Name',
        'USER_TYPE_ID' => 'string',
        'MANDATORY' => 'Y',
        'EDIT_FORM_LABEL' => 'New Label',
        'SORT' => '100',
        'SETTINGS' => json_encode(['min' => '2023-10-01T00:00:00Z', 'max' => '2023-12-31T00:00:00Z']),
    ];

    $result = $serviceBuilder
        ->getCRMScope()
        ->dealUserfield()
        ->update($userfieldItemId, $userfieldFieldsToUpdate);

    if ($result->isSuccess()) {
        print($result->getItemResult());
    } else {
        print("Update failed.");
    }
} catch (Throwable $e) {
    print("Error: " . $e->getMessage());
}

//generated_example_code_finish