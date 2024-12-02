<?php
declare(strict_types=1);

use Bitrix24\SDK\Tests\Integration\Fabric;

require_once 'tests/bootstrap.php';
$serviceBuilder = Fabric::getServiceBuilder(true);

//generated_example_code_start

try {
    $filterFields = [
        'ACTIVE' => 'Y',
        'IS_ONLINE' => 'Y',
        'DATE_REGISTER' => (new DateTime())->format(DATE_ATOM),
    ];

    $result = $serviceBuilder->getUserScope()->search($filterFields);

    foreach ($result->getUsers() as $user) {
        print("ID: {$user->ID}\n");
        print("Name: {$user->NAME}\n");
        print("Last Name: {$user->LAST_NAME}\n");
        print("Email: {$user->EMAIL}\n");
        print("Date Registered: {$user->DATE_REGISTER->format(DATE_ATOM)}\n");
        print("Last Login: {$user->LAST_LOGIN->format(DATE_ATOM)}\n");
        print("Is Online: {$user->IS_ONLINE}\n");
        print("Time Zone: {$user->TIME_ZONE}\n");
        print("Personal Birthday: {$user->PERSONAL_BIRTHDAY->format(DATE_ATOM)}\n");
        print("Employment Date: {$user->UF_EMPLOYMENT_DATE->format(DATE_ATOM)}\n");
        print("\n");
    }
} catch (Throwable $e) {
    print("Error: " . $e->getMessage());
}

//generated_example_code_finish