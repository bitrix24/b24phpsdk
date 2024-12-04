<?php
declare(strict_types=1);

use Bitrix24\SDK\Tests\Integration\Fabric;

require_once 'tests/bootstrap.php';
$serviceBuilder = Fabric::getServiceBuilder(true);

//generated_example_code_start

```php
try {
    $agreementId = 1; // Example agreement ID
    $replace = [
        'button_caption' => 'Accept',
        'fields' => [
            'name' => 'John Doe',
            'email' => 'john.doe@example.com',
        ],
    ];

    $result = $serviceBuilder
        ->getUserConsentScope()
        ->agreement()
        ->text($agreementId, $replace);

    $itemResult = $result->text();
    print($itemResult->LABEL);
    print($itemResult->TEXT);
} catch (Throwable $e) {
    // Handle the exception
    print('Error: ' . $e->getMessage());
}
```

//generated_example_code_finish