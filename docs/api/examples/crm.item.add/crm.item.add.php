<?php
declare(strict_types=1);

use Bitrix24\SDK\Tests\Integration\Fabric;

require_once 'tests/bootstrap.php';
$serviceBuilder = Fabric::getServiceBuilder(true);

//generated_example_code_start

try {
    $entityTypeId = 1; // Example entity type ID
    $fields = [
        'title' => 'New Item',
        'createdTime' => (new DateTime())->format(DateTime::ATOM),
        'updatedTime' => (new DateTime())->format(DateTime::ATOM),
        'begindate' => (new DateTime())->format(DateTime::ATOM),
        'closedate' => (new DateTime())->format(DateTime::ATOM),
        // Add other necessary fields as required
    ];
    
    $result = $serviceBuilder
        ->getCRMScope()
        ->item()
        ->add($entityTypeId, $fields);

    print("ID: " . $result->item()->id . PHP_EOL);
    print("Title: " . $result->item()->title . PHP_EOL);
    print("Created By: " . $result->item()->createdBy . PHP_EOL);
    print("Updated By: " . $result->item()->updatedBy . PHP_EOL);
    print("Created Time: " . $result->item()->createdTime->format(DateTime::ATOM) . PHP_EOL);
    print("Updated Time: " . $result->item()->updatedTime->format(DateTime::ATOM) . PHP_EOL);
} catch (Throwable $e) {
    print("Error: " . $e->getMessage() . PHP_EOL);
}

//generated_example_code_finish