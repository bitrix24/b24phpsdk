<?php
declare(strict_types=1);

use Bitrix24\SDK\Tests\Integration\Fabric;

require_once 'tests/bootstrap.php';
$serviceBuilder = Fabric::getServiceBuilder(true);

//generated_example_code_start

try {
    $fields = [
        'OWNER_ID' => 1,
        'OWNER_TYPE_ID' => 2,
        'TYPE_ID' => 3,
        'SUBJECT' => 'Meeting',
        'START_TIME' => (new DateTime())->format(DateTime::ATOM),
        'END_TIME' => (new DateTime('+1 hour'))->format(DateTime::ATOM),
        'DESCRIPTION' => 'Discuss project updates',
        'RESPONSIBLE_ID' => 1,
        'STATUS' => 'completed',
        'PRIORITY' => 1,
    ];

    $result = $serviceBuilder
        ->getCRMScope()
        ->activity()
        ->add($fields);

    print($result->getId());
} catch (Throwable $e) {
    print('Error: ' . $e->getMessage());
}

//generated_example_code_finish