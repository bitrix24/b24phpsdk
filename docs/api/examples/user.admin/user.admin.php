<?php
declare(strict_types=1);

use Bitrix24\SDK\Tests\Integration\Fabric;

require_once 'tests/bootstrap.php';
$serviceBuilder = Fabric::getServiceBuilder(true);

//generated_example_code_start

try {
    $result = $serviceBuilder->getMainScope()->isCurrentUserHasAdminRights();
    if ($result->isAdmin()) {
        print("Current user has admin rights.");
    } else {
        print("Current user does not have admin rights.");
    }
} catch (Throwable $e) {
    print("An error occurred: " . $e->getMessage());
}

//generated_example_code_finish