<?php

namespace Bitrix24\SDK\Tests\Unit\Services\Workflows\Common;

use Bitrix24\SDK\Services\Workflows\Common\WorkflowDocumentId;
use Generator;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

#[CoversClass(WorkflowDocumentId::class)]
class WorkflowDocumentIdTest extends TestCase
{
    #[DataProvider('documentIdDataProvider')]
    public function testInitFromArray(array $documentId, int $dealId): void
    {
        $documentId = WorkflowDocumentId::initFromArray($documentId);
        $this->assertEquals(
            $dealId,
            $documentId->getId()
        );
    }

    public static function documentIdDataProvider(): Generator
    {
        yield 'deal' => [
            [
                "crm",
                "CCrmDocumentDeal",
                "DEAL_165752"
            ],
            165752
        ];
        yield 'task' => [
            [
                "tasks",
                "Bitrix\\Tasks\\Integration\\Bizproc\\Document\\Task",
                "2"
            ],
            2
        ];
        yield 'smart process' => [
            [
                "crm",
                "Bitrix\\Crm\\Integration\\BizProc\\Document\\Dynamic",
                "DYNAMIC_1032_4"
            ],
            4
        ];
    }
}