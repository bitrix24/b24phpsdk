<?php
declare(strict_types=1);

use Bitrix24\SDK\Tests\Integration\Fabric;

require_once 'tests/bootstrap.php';
$serviceBuilder = Fabric::getServiceBuilder(true);

//generated_example_code_start

try {
    $result = $serviceBuilder->getPlacementScope()
        ->userFieldType()
        ->update(
            'custom_user_type',  // userTypeId
            'https://example.com/handler',  // handlerUrl
            'Custom User Type',  // title
            'Description of custom user type'  // description
        );

    if ($result->isSuccess()) {
        print("Update successful.");
    } else {
        print("Update failed.");
    }
} catch (Throwable $e) {
    print("An error occurred: " . $e->getMessage());
}

//generated_example_code_finish