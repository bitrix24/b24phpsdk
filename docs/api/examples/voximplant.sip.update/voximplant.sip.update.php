<?php
declare(strict_types=1);

use Bitrix24\SDK\Tests\Integration\Fabric;

require_once 'tests/bootstrap.php';
$serviceBuilder = Fabric::getServiceBuilder(true);

//generated_example_code_start

try {
    $sipConfigId = 123; // Example SIP Config ID
    $pbxType = new Bitrix24\SDK\Services\Telephony\Common\PbxType('cloud'); // Example PBX Type
    $title = 'New SIP Line Title'; // New title
    $serverUrl = 'https://example.com'; // Server URL
    $login = 'user'; // Login
    $password = 'password'; // Password

    $result = $serviceBuilder
        ->getTelephonyScope()
        ->getVoximplantScope()
        ->getSipScope()
        ->update($sipConfigId, $pbxType, $title, $serverUrl, $login, $password);
    
    if ($result->isSuccess()) {
        print_r($result->getCoreResponse()->getResponseData()->getResult());
    } else {
        print('Update failed.');
    }
} catch (Throwable $e) {
    print('An error occurred: ' . $e->getMessage());
}

//generated_example_code_finish