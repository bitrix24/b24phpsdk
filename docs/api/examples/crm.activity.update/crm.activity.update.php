<?php
declare(strict_types=1);

use Bitrix24\SDK\Tests\Integration\Fabric;

require_once 'tests/bootstrap.php';
$serviceBuilder = Fabric::getServiceBuilder(true);

//generated_example_code_start

try {
    $itemId = 123; // Example item ID
    $fields = [
        'ID' => 123,
        'OWNER_ID' => 1,
        'OWNER_TYPE_ID' => '2',
        'TYPE_ID' => '3',
        'PROVIDER_ID' => 'provider_id',
        'PROVIDER_TYPE_ID' => 'provider_type_id',
        'PROVIDER_GROUP_ID' => 'provider_group_id',
        'ASSOCIATED_ENTITY_ID' => 456,
        'SUBJECT' => 'Updated Subject',
        'START_TIME' => (new DateTime())->format(DateTime::ATOM),
        'END_TIME' => (new DateTime('+1 hour'))->format(DateTime::ATOM),
        'DEADLINE' => (new DateTime('+2 hours'))->format(DateTime::ATOM),
        'COMPLETED' => true,
        'STATUS' => 'completed',
        'RESPONSIBLE_ID' => 'responsible_id',
        'PRIORITY' => 'high',
        'NOTIFY_TYPE' => 'email',
        'NOTIFY_VALUE' => 1,
        'DESCRIPTION' => 'Updated description',
        'DESCRIPTION_TYPE' => 'text',
        'DIRECTION' => 'incoming',
        'LOCATION' => 'Office',
        'CREATED' => (new DateTime())->format(DateTime::ATOM),
        'AUTHOR_ID' => 'author_id',
        'LAST_UPDATED' => (new DateTime())->format(DateTime::ATOM),
        'EDITOR_ID' => 'editor_id',
        'SETTINGS' => 'some settings',
        'ORIGIN_ID' => 'origin_id',
        'ORIGINATOR_ID' => 'originator_id',
        'RESULT_STATUS' => 1,
        'RESULT_STREAM' => 2,
        'RESULT_SOURCE_ID' => 'result_source_id',
        'PROVIDER_PARAMS' => 'provider_params',
        'PROVIDER_DATA' => 'provider_data',
        'RESULT_MARK' => 1,
        'RESULT_VALUE' => 'result_value',
        'RESULT_SUM' => '100',
        'RESULT_CURRENCY_ID' => 'USD',
        'AUTOCOMPLETE_RULE' => 1,
        'BINDINGS' => 'bindings',
        'COMMUNICATIONS' => 'communications',
        'FILES' => 'files',
        'WEBDAV_ELEMENTS' => 'webdav_elements',
    ];

    $result = $serviceBuilder->getCRMScope()->activity()->update($itemId, $fields);
    
    if ($result->isSuccess()) {
        print("Item updated successfully: " . json_encode($result->getCoreResponse()->getResponseData()->getResult()));
    } else {
        print("Failed to update item.");
    }
} catch (Throwable $e) {
    print("An error occurred: " . $e->getMessage());
}

//generated_example_code_finish