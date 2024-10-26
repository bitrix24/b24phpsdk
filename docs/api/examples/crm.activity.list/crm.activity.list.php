<?php
declare(strict_types=1);

use Bitrix24\SDK\Tests\Integration\Fabric;

require_once 'tests/bootstrap.php';
$serviceBuilder = Fabric::getServiceBuilder(true);

//generated_example_code_start

try {
    $order = []; // Define your order array as needed
    $filter = [
        'OWNER_ID' => 1, // Example filter
        'COMPLETED' => false,
    ];
    $select = [
        'ID', 'OWNER_ID', 'OWNER_TYPE_ID', 'TYPE_ID', 'SUBJECT', 
        'START_TIME', 'END_TIME', 'DEADLINE', 'COMPLETED', 
        'STATUS', 'RESPONSIBLE_ID', 'PRIORITY', 'DESCRIPTION', 
        'LOCATION', 'CREATED', 'AUTHOR_ID', 'LAST_UPDATED'
    ];
    $start = 0; // Starting point for pagination

    $activitiesResult = $serviceBuilder
        ->getCRMScope()
        ->activity()
        ->list($order, $filter, $select, $start);

    foreach ($activitiesResult->getActivities() as $activity) {
        print($activity->ID);
        print($activity->OWNER_ID);
        print($activity->OWNER_TYPE_ID);
        print($activity->TYPE_ID);
        print($activity->SUBJECT);
        print($activity->START_TIME->format(DATE_ATOM)); // Format DateTime to Atom
        print($activity->END_TIME->format(DATE_ATOM));
        print($activity->DEADLINE->format(DATE_ATOM));
        print($activity->COMPLETED ? 'Yes' : 'No');
        print($activity->STATUS);
        print($activity->RESPONSIBLE_ID);
        print($activity->PRIORITY);
        print($activity->DESCRIPTION);
        print($activity->LOCATION);
        print($activity->CREATED->format(DATE_ATOM));
        print($activity->AUTHOR_ID);
        print($activity->LAST_UPDATED->format(DATE_ATOM));
    }
} catch (Throwable $e) {
    // Handle the exception
    print('Error: ' . $e->getMessage());
}

//generated_example_code_finish