<?php
declare(strict_types=1);

use Bitrix24\SDK\Tests\Integration\Fabric;

require_once 'tests/bootstrap.php';
$serviceBuilder = Fabric::getServiceBuilder(true);

//generated_example_code_start

try {
    $select = ['id', 'name', 'price', 'active', 'available', 'dateCreate'];
    $filter = ['active' => 'Y'];
    $order = ['name' => 'ASC'];
    $start = 0;

    $result = $serviceBuilder
        ->getCatalogScope()
        ->product()
        ->list($select, $filter, $order, $start);

    foreach ($result->getProducts() as $itemResult) {
        print("ID: {$itemResult->id}\n");
        print("Name: {$itemResult->name}\n");
        print("Active: {$itemResult->active}\n");
        print("Available: {$itemResult->available}\n");
        print("Date Created: {$itemResult->dateCreate->format(DATE_ATOM)}\n");
    }
} catch (Throwable $e) {
    print("Error: {$e->getMessage()}\n");
}

//generated_example_code_finish