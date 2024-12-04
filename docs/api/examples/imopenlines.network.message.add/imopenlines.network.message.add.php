<?php
declare(strict_types=1);

use Bitrix24\SDK\Tests\Integration\Fabric;

require_once 'tests/bootstrap.php';
$serviceBuilder = Fabric::getServiceBuilder(true);

//generated_example_code_start

try {
    $openLineCode = 'your_open_line_code'; // Replace with your actual open line code
    $recipientUserId = 123; // Replace with the actual recipient user ID
    $message = 'Hello, this is a test message.'; // Replace with your actual message
    $isMakeUrlPreview = true; // Set to true or false based on your requirement
    $attach = null; // Set to an array if you have attachments
    $keyboard = null; // Set to an array if you have a keyboard layout

    $result = $serviceBuilder
        ->getIMOpenLinesScope()
        ->getNetwork()
        ->messageAdd($openLineCode, $recipientUserId, $message, $isMakeUrlPreview, $attach, $keyboard);

    if ($result->isSuccess()) {
        print($result->getCoreResponse()->getResponseData()->getResult()[0]);
    } else {
        print('Failed to send message.');
    }
} catch (Throwable $e) {
    print('Error: ' . $e->getMessage());
}

//generated_example_code_finish