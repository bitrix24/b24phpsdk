<?php
declare(strict_types=1);

use Bitrix24\SDK\Tests\Integration\Fabric;

require_once 'tests/bootstrap.php';
$serviceBuilder = Fabric::getServiceBuilder(true);

//generated_example_code_start

try {
    $pbxType = new Bitrix24\SDK\Services\Telephony\Common\PbxType('your_pbx_type'); // Replace 'your_pbx_type' with actual value
    $title = 'Your SIP Line Title';
    $serverUrl = 'https://your.server.url';
    $login = 'your_login';
    $password = 'your_password';

    $result = $serviceBuilder
        ->getTelephonyScope()
        ->voximplant()
        ->sip()
        ->add($pbxType, $title, $serverUrl, $login, $password);

    $itemResult = $result->getLine();
    print("ID: " . $itemResult->ID . "\n");
    print("TYPE: " . $itemResult->TYPE . "\n");
    print("CONFIG_ID: " . $itemResult->CONFIG_ID . "\n");
    print("REG_ID: " . $itemResult->REG_ID . "\n");
    print("SERVER: " . $itemResult->SERVER . "\n");
    print("LOGIN: " . $itemResult->LOGIN . "\n");
    print("PASSWORD: " . $itemResult->PASSWORD . "\n");
} catch (Throwable $e) {
    print("An error occurred: " . $e->getMessage());
}

//generated_example_code_finish