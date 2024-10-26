<?php
declare(strict_types=1);

use Bitrix24\SDK\Tests\Integration\Fabric;

require_once 'tests/bootstrap.php';
$serviceBuilder = Fabric::getServiceBuilder(true);

//generated_example_code_start

```php
try {
    $lineId = 'your_line_id'; // non-empty-string
    $toNumber = 'your_to_number'; // non-empty-string
    $text = 'Your text message'; // non-empty-string
    $voiceCode = null; // ?(non-empty-string)|null

    $result = $serviceBuilder
        ->getTelephonyScope()
        ->voximplant()
        ->infoCall()
        ->startWithText($lineId, $toNumber, $text, $voiceCode);

    $itemResult = $result->getCallResult();

    print("CALL_ID: " . $itemResult->CALL_ID . "\n");
    print("RESULT: " . ($itemResult->RESULT ? 'true' : 'false') . "\n");

} catch (Throwable $e) {
    print("Error: " . $e->getMessage() . "\n");
}
```

//generated_example_code_finish