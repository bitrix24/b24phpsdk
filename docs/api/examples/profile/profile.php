<?php
declare(strict_types=1);

use Bitrix24\SDK\Tests\Integration\Fabric;

require_once 'tests/bootstrap.php';
$serviceBuilder = Fabric::getServiceBuilder(true);

//generated_example_code_start

try {
    $userProfileResult = $serviceBuilder->getMainScope()->getCurrentUserProfile();
    $userProfile = $userProfileResult->getUserProfile();
    
    print("ID: " . $userProfile->ID . "\n");
    print("Name: " . $userProfile->NAME . "\n");
    print("Last Name: " . $userProfile->LAST_NAME . "\n");
    print("Gender: " . $userProfile->PERSONAL_GENDER . "\n");
    print("Photo: " . $userProfile->PERSONAL_PHOTO . "\n");
    print("Time Zone: " . $userProfile->TIME_ZONE . "\n");
    print("Time Zone Offset: " . $userProfile->TIME_ZONE_OFFSET . "\n");
    print("Status: " . $userProfile->getStatus() . "\n");
} catch (\Throwable $e) {
    print("Error: " . $e->getMessage() . "\n");
}

//generated_example_code_finish