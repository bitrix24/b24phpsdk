<?php
declare(strict_types=1);

use Bitrix24\SDK\Tests\Integration\Fabric;

require_once 'tests/bootstrap.php';
$serviceBuilder = Fabric::getServiceBuilder(true);

//generated_example_code_start

try {
    $result = $serviceBuilder->getIMScope()
        ->notify()
        ->fromSystem(
            123, // $userId
            'This is a test message.', // $message
            null, // $forEmailChannelMessage
            null, // $notificationTag
            null, // $subTag
            null // $attachment
        );

    print($result->getId());
} catch (Throwable $e) {
    // Handle exception
    print('Error: ' . $e->getMessage());
}

//generated_example_code_finish