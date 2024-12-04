<?php
declare(strict_types=1);

use Bitrix24\SDK\Tests\Integration\Fabric;

require_once 'tests/bootstrap.php';
$serviceBuilder = Fabric::getServiceBuilder(true);

//generated_example_code_start

```php
try {
    $openLineCode = 'your_open_line_code'; // Replace with your actual open line code
    $result = $serviceBuilder
        ->getIMOpenLinesScope()
        ->network()
        ->join($openLineCode);

    // Process the result
    print($result->getId());
} catch (Throwable $e) {
    // Handle the exception
    print('Error: ' . $e->getMessage());
}
```

//generated_example_code_finish