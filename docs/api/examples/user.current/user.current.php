<?php
declare(strict_types=1);

use Bitrix24\SDK\Tests\Integration\Fabric;

require_once 'tests/bootstrap.php';
$serviceBuilder = Fabric::getServiceBuilder(true);

//generated_example_code_start

try {
    $userResult = $serviceBuilder
        ->getUserScope()
        ->current();

    $userItem = $userResult->user();

    print("ID: " . $userItem->ID . PHP_EOL);
    print("XML_ID: " . $userItem->XML_ID . PHP_EOL);
    print("ACTIVE: " . ($userItem->ACTIVE ? 'Yes' : 'No') . PHP_EOL);
    print("NAME: " . $userItem->NAME . PHP_EOL);
    print("LAST_NAME: " . $userItem->LAST_NAME . PHP_EOL);
    print("SECOND_NAME: " . $userItem->SECOND_NAME . PHP_EOL);
    print("TITLE: " . $userItem->TITLE . PHP_EOL);
    print("EMAIL: " . $userItem->EMAIL . PHP_EOL);
    print("LAST_LOGIN: " . $userItem->LAST_LOGIN->format(DATE_ATOM) . PHP_EOL);
    print("DATE_REGISTER: " . $userItem->DATE_REGISTER->format(DATE_ATOM) . PHP_EOL);
    print("TIME_ZONE: " . $userItem->TIME_ZONE . PHP_EOL);
    print("IS_ONLINE: " . ($userItem->IS_ONLINE ? 'Yes' : 'No') . PHP_EOL);
    print("TIME_ZONE_OFFSET: " . $userItem->TIME_ZONE_OFFSET . PHP_EOL);
    print("PERSONAL_BIRTHDAY: " . $userItem->PERSONAL_BIRTHDAY->format(DATE_ATOM) . PHP_EOL);
    print("UF_EMPLOYMENT_DATE: " . $userItem->UF_EMPLOYMENT_DATE->format(DATE_ATOM) . PHP_EOL);
    print("PERSONAL_CITY: " . $userItem->PERSONAL_CITY . PHP_EOL);
    print("WORK_PHONE: " . $userItem->WORK_PHONE . PHP_EOL);
} catch (Throwable $e) {
    print("Error: " . $e->getMessage() . PHP_EOL);
}

//generated_example_code_finish