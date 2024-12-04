<?php
declare(strict_types=1);

use Bitrix24\SDK\Tests\Integration\Fabric;

require_once 'tests/bootstrap.php';
$serviceBuilder = Fabric::getServiceBuilder(true);

//generated_example_code_start

```php
try {
    $fields = [
        'NAME' => 'John',
        'LAST_NAME' => 'Doe',
        'EMAIL' => 'john.doe@example.com',
        'PERSONAL_BIRTHDAY' => (new DateTime('1990-01-01'))->format(DateTime::ATOM),
        'EXTRANET' => 'N', // Required field
    ];
    $messageText = 'Welcome to our platform!';

    $result = $serviceBuilder
        ->getUserScope()
        ->add($fields, $messageText);

    print($result->getId());
} catch (Throwable $e) {
    print('Error: ' . $e->getMessage());
}
```

//generated_example_code_finish