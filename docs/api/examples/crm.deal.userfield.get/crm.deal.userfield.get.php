<?php
declare(strict_types=1);

use Bitrix24\SDK\Tests\Integration\Fabric;

require_once 'tests/bootstrap.php';
$serviceBuilder = Fabric::getServiceBuilder(true);

//generated_example_code_start

try {
    $userfieldItemId = 123; // Replace with the actual userfield item ID
    $result = $serviceBuilder->getCRMScope()
        ->dealUserfield()
        ->get($userfieldItemId);
    
    $itemResult = $result->userfieldItem();
    
    print($itemResult->getId());
    print($itemResult->getFieldName());
    print($itemResult->getUserTypeId());
    print($itemResult->getXmlId());
    print($itemResult->getSort());
    print($itemResult->getMultiple());
    print($itemResult->getMandatory());
    print($itemResult->getShowFilter());
    print($itemResult->getShowInList());
    print($itemResult->getEditInList());
    print($itemResult->getIsSearchable());
    print($itemResult->getEditFormLabel());
    print($itemResult->getListColumnLabel());
    print($itemResult->getListFilterLabel());
    print($itemResult->getErrorMessage());
    print($itemResult->getHelpMessage());
    print($itemResult->getSettings());
} catch (Throwable $e) {
    print("Error: " . $e->getMessage());
}

//generated_example_code_finish