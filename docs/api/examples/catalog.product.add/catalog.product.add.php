<?php
declare(strict_types=1);

use Bitrix24\SDK\Tests\Integration\Fabric;

require_once 'tests/bootstrap.php';
$serviceBuilder = Fabric::getServiceBuilder(true);

//generated_example_code_start

```php
try {
    $productFields = [
        'active' => true,
        'available' => true,
        'bundle' => false,
        'code' => 'example-code',
        'createdBy' => 1,
        'dateActiveFrom' => (new DateTime())->format(DateTime::ATOM),
        'dateActiveTo' => (new DateTime('+1 month'))->format(DateTime::ATOM),
        'dateCreate' => (new DateTime())->format(DateTime::ATOM),
        'detailText' => 'Example detail text.',
        'id' => 0,
        'iblockId' => 1,
        'iblockSectionId' => 1,
        'modifiedBy' => 1,
        'name' => 'Example Product',
        'previewText' => 'Example preview text.',
        'xmlId' => 'example-xml-id',
    ];

    $result = $serviceBuilder
        ->getCatalogScope()
        ->product()
        ->add($productFields);

    $itemResult = $result->product();
    
    print($itemResult->active);
    print($itemResult->available);
    print($itemResult->bundle);
    print($itemResult->code);
    print($itemResult->createdBy);
    print($itemResult->dateActiveFrom->format(DateTime::ATOM));
    print($itemResult->dateActiveTo->format(DateTime::ATOM));
    print($itemResult->dateCreate->format(DateTime::ATOM));
    print($itemResult->id);
    print($itemResult->iblockId);
    print($itemResult->iblockSectionId);
    print($itemResult->modifiedBy);
    print($itemResult->name);
    print($itemResult->previewText);
    print($itemResult->xmlId);
} catch (Throwable $e) {
    // Handle exception
    print('Error: ' . $e->getMessage());
}
```

//generated_example_code_finish