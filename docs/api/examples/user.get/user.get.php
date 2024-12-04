<?php
declare(strict_types=1);

use Bitrix24\SDK\Tests\Integration\Fabric;

require_once 'tests/bootstrap.php';
$serviceBuilder = Fabric::getServiceBuilder(true);

//generated_example_code_start

try {
    $order = []; // Define your order array as needed
    $filter = []; // Define your filter array as needed
    $isAdminMode = false; // Set admin mode as needed

    $result = $serviceBuilder->getUserScope()
        ->get()
        ->get($order, $filter, $isAdminMode);

    foreach ($result->getUsers() as $user) {
        print("ID: " . $user->ID . "\n");
        print("XML_ID: " . $user->XML_ID . "\n");
        print("ACTIVE: " . ($user->ACTIVE ? 'true' : 'false') . "\n");
        print("NAME: " . $user->NAME . "\n");
        print("LAST_NAME: " . $user->LAST_NAME . "\n");
        print("SECOND_NAME: " . $user->SECOND_NAME . "\n");
        print("TITLE: " . $user->TITLE . "\n");
        print("EMAIL: " . $user->EMAIL . "\n");
        print("LAST_LOGIN: " . $user->LAST_LOGIN->format(DATE_ATOM) . "\n");
        print("DATE_REGISTER: " . $user->DATE_REGISTER->format(DATE_ATOM) . "\n");
        print("TIME_ZONE: " . $user->TIME_ZONE . "\n");
        print("IS_ONLINE: " . ($user->IS_ONLINE ? 'true' : 'false') . "\n");
        print("TIME_ZONE_OFFSET: " . $user->TIME_ZONE_OFFSET . "\n");
        print("PERSONAL_GENDER: " . $user->PERSONAL_GENDER . "\n");
        print("PERSONAL_BIRTHDAY: " . $user->PERSONAL_BIRTHDAY->format(DATE_ATOM) . "\n");
        print("PERSONAL_PHOTO: " . $user->PERSONAL_PHOTO . "\n");
        print("PERSONAL_MOBILE: " . $user->PERSONAL_MOBILE . "\n");
        print("PERSONAL_CITY: " . $user->PERSONAL_CITY . "\n");
        print("WORK_PHONE: " . $user->WORK_PHONE . "\n");
    }
} catch (\Throwable $e) {
    print("Error: " . $e->getMessage() . "\n");
}

//generated_example_code_finish