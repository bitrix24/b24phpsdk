<?php
declare(strict_types=1);

use Bitrix24\SDK\Tests\Integration\Fabric;

require_once 'tests/bootstrap.php';
$serviceBuilder = Fabric::getServiceBuilder(true);

//generated_example_code_start

try {
    $callId = 'exampleCallId'; // non-empty-string
    $callRecordFileName = 'exampleCallRecord.mp3'; // non-empty-string

    $result = $serviceBuilder
        ->getTelephonyScope()
        ->externalCall()
        ->attachCallRecordInBase64($callId, $callRecordFileName)
        ->getRecordUploadedResult();

    print($result->FILE_ID);
} catch (Throwable $e) {
    // Handle the exception as needed
    print('Error: ' . $e->getMessage());
}

//generated_example_code_finish