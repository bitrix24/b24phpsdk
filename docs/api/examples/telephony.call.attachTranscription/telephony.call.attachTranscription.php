<?php
declare(strict_types=1);

use Bitrix24\SDK\Tests\Integration\Fabric;

require_once 'tests/bootstrap.php';
$serviceBuilder = Fabric::getServiceBuilder(true);

//generated_example_code_start

try {
    $callId = '12345'; // Example call ID
    $money = new Money(1000, new Currency('USD')); // Example money object
    $messages = [
        new TranscriptMessage('user', new DateTime('2023-10-01T10:00:00Z'), new DateTime('2023-10-01T10:01:00Z'), 'Hello, how can I help you?'),
        new TranscriptMessage('user', new DateTime('2023-10-01T10:01:00Z'), new DateTime('2023-10-01T10:02:00Z'), 'I have a question about my order.')
    ];

    $result = $serviceBuilder->getTelephonyScope()
        ->call()
        ->attachTranscription($callId, $money, $messages);

    $transcriptItem = $result->getTranscriptAttachItem();
    print($transcriptItem->TRANSCRIPT_ID);
} catch (Throwable $e) {
    print('Error: ' . $e->getMessage());
}

//generated_example_code_finish