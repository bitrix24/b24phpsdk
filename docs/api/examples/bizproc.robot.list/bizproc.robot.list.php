<?php
declare(strict_types=1);

use Bitrix24\SDK\Tests\Integration\Fabric;

require_once 'tests/bootstrap.php';
$serviceBuilder = Fabric::getServiceBuilder(true);

//generated_example_code_start

try {
    $result = $serviceBuilder
        ->getBizProcScope()
        ->robot()
        ->list();

    foreach ($result->getRobots() as $robot) {
        print($robot->code);
        print($robot->name);
        print($robot->handlerUrl);
        print($robot->authUserId);
        print($robot->isUseSubscription ? 'Yes' : 'No');
        print($robot->isUsePlacement ? 'Yes' : 'No');
        if ($robot->createdDate instanceof DateTime) {
            print($robot->createdDate->format(DateTime::ATOM));
        }
    }
} catch (Throwable $e) {
    // Handle the exception
    print('Error: ' . $e->getMessage());
}

//generated_example_code_finish