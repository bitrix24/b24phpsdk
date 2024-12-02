<?php
declare(strict_types=1);

use Bitrix24\SDK\Tests\Integration\Fabric;

require_once 'tests/bootstrap.php';
$serviceBuilder = Fabric::getServiceBuilder(true);

//generated_example_code_start

```php
try {
    $result = $serviceBuilder
        ->getBizProcScope()
        ->activity()
        ->list();

    $activities = $result->getActivities();
    
    foreach ($activities as $activity) {
        print($activity->name); // Assuming 'name' is a public property of activity
        print($activity->description); // Assuming 'description' is a public property of activity
        // Handle DateTime fields if applicable
        if (isset($activity->createdDate)) {
            print($activity->createdDate->format(DATE_ATOM));
        }
    }
} catch (Throwable $e) {
    print('Error: ' . $e->getMessage());
}
```

//generated_example_code_finish