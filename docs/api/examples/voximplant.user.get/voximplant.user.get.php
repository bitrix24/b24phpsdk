<?php
declare(strict_types=1);

use Bitrix24\SDK\Tests\Integration\Fabric;

require_once 'tests/bootstrap.php';
$serviceBuilder = Fabric::getServiceBuilder(true);

//generated_example_code_start

try {
    $userIds = [1, 2, 3]; // Example user IDs
    $result = $serviceBuilder
        ->getTelephonyScope()
        ->voximplant()
        ->user()
        ->get($userIds);

    $users = $result->getUsers();
    foreach ($users as $user) {
        print("ID: " . $user->ID . "\n");
        print("Default Line: " . $user->DEFAULT_LINE . "\n");
        print("Phone Enabled: " . ($user->PHONE_ENABLED ? 'Yes' : 'No') . "\n");
        print("SIP Server: " . $user->SIP_SERVER . "\n");
        print("SIP Login: " . $user->SIP_LOGIN . "\n");
        print("SIP Password: " . $user->SIP_PASSWORD . "\n");
        print("Inner Number: " . $user->INNER_NUMBER . "\n");
    }
} catch (Throwable $e) {
    print("Error: " . $e->getMessage() . "\n");
}

//generated_example_code_finish