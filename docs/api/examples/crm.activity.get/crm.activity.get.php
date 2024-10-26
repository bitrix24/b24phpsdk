<?php
declare(strict_types=1);

use Bitrix24\SDK\Tests\Integration\Fabric;

require_once 'tests/bootstrap.php';
$serviceBuilder = Fabric::getServiceBuilder(true);

//generated_example_code_start

try {
    $entityId = 123; // Example entity ID
    $result = $serviceBuilder->getCRMScope()
        ->activity()
        ->get($entityId);
    
    $activityItem = $result->activity();
    
    print("ID: " . $activityItem->ID . "\n");
    print("Owner ID: " . $activityItem->OWNER_ID . "\n");
    print("Owner Type ID: " . $activityItem->OWNER_TYPE_ID . "\n");
    print("Type ID: " . $activityItem->TYPE_ID . "\n");
    print("Provider ID: " . $activityItem->PROVIDER_ID . "\n");
    print("Provider Type ID: " . $activityItem->PROVIDER_TYPE_ID . "\n");
    print("Subject: " . $activityItem->SUBJECT . "\n");
    print("Start Time: " . $activityItem->START_TIME . "\n");
    print("End Time: " . $activityItem->END_TIME . "\n");
    print("Deadline: " . $activityItem->DEADLINE->format(DATE_ATOM) . "\n");
    print("Completed: " . ($activityItem->COMPLETED ? 'Yes' : 'No') . "\n");
    print("Status: " . $activityItem->STATUS . "\n");
    print("Responsible ID: " . $activityItem->RESPONSIBLE_ID . "\n");
    print("Priority: " . $activityItem->PRIORITY . "\n");
    print("Description: " . $activityItem->DESCRIPTION . "\n");
    print("Location: " . $activityItem->LOCATION . "\n");
    print("Created: " . $activityItem->CREATED->format(DATE_ATOM) . "\n");
    print("Author ID: " . $activityItem->AUTHOR_ID . "\n");
    print("Last Updated: " . $activityItem->LAST_UPDATED->format(DATE_ATOM) . "\n");
} catch (Throwable $e) {
    print("Error: " . $e->getMessage() . "\n");
}

//generated_example_code_finish