<?php

declare(strict_types=1);

namespace Bitrix24\SDK\Tests\Unit\OpenApi\Domain\ResultItem\Verification;

use Bitrix24\SDK\OpenApi\Domain\ResultItem\Payload\ResultItemPayload;
use Bitrix24\SDK\OpenApi\Domain\ResultItem\Payload\ResultItemPayloadField;
use Bitrix24\SDK\OpenApi\Domain\ResultItem\Verification\ResultItemVerificationApplier;
use Bitrix24\SDK\OpenApi\Domain\ResultItem\Verification\ResultItemVerificationReport;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

final class ResultItemVerificationApplierTest extends TestCase
{
    #[Test]
    public function itAppliesOnlySafeUpdatesFromTheVerificationReport(): void
    {
        $resultItemPayload = new ResultItemPayload(
            method: 'im.dialog.get',
            object: 'result-item',
            generatedFrom: ['openapi', 'b24restdocs'],
            fields: [
                new ResultItemPayloadField(
                    code: 'id',
                    sourceType: 'integer',
                    phpdocType: 'int',
                    format: null,
                    required: true,
                    nullable: false,
                    source: 'openapi',
                ),
                new ResultItemPayloadField(
                    code: 'date_create',
                    sourceType: 'datetime',
                    phpdocType: \Carbon\CarbonImmutable::class,
                    format: 'date-time',
                    required: true,
                    nullable: false,
                    source: 'b24restdocs',
                ),
                new ResultItemPayloadField(
                    code: 'name',
                    sourceType: 'string',
                    phpdocType: 'string|null',
                    format: null,
                    required: true,
                    nullable: true,
                    source: 'b24restdocs',
                ),
            ],
            sections: [],
        );

        $resultItemVerificationReport = new ResultItemVerificationReport(
            method: 'im.dialog.get',
            confirmedFields: [],
            missingFields: [],
            unexpectedFields: [[
                'action' => 'add_field',
                'code' => 'status',
                'section' => null,
                'source_type' => 'string',
                'phpdoc_type' => 'string|null',
                'format' => null,
                'nullable' => true,
            ], [
                'action' => 'review_structural_addition',
                'code' => 'permissions',
                'section' => null,
                'source_type' => 'object',
                'phpdoc_type' => 'array',
                'format' => null,
                'nullable' => false,
            ]],
            typeMismatches: [[
                'action' => 'review_type_mismatch',
                'code' => 'id',
                'section' => null,
                'expected_source_type' => 'integer',
                'actual_source_type' => 'string',
                'expected_phpdoc_type' => 'int',
                'actual_phpdoc_type' => 'string',
                'expected_format' => null,
                'actual_format' => null,
            ]],
            nullabilityObservations: [[
                'action' => 'mark_nullable',
                'code' => 'date_create',
                'section' => null,
            ], [
                'action' => 'mark_nullable',
                'code' => 'name',
                'section' => null,
            ]],
        );

        $updatedPayload = (new ResultItemVerificationApplier())->apply($resultItemPayload, $resultItemVerificationReport);

        self::assertCount(4, $updatedPayload->fields);
        self::assertSame('status', $updatedPayload->fields[3]->code);
        self::assertSame('string', $updatedPayload->fields[3]->sourceType);
        self::assertSame('string|null', $updatedPayload->fields[3]->phpdocType);
        self::assertTrue($updatedPayload->fields[3]->nullable);
        self::assertTrue($updatedPayload->fields[1]->nullable);
        self::assertSame('Carbon\\CarbonImmutable|null', $updatedPayload->fields[1]->phpdocType);
        self::assertTrue($updatedPayload->fields[2]->nullable);
        self::assertSame('string|null', $updatedPayload->fields[2]->phpdocType);
        self::assertSame('integer', $updatedPayload->fields[0]->sourceType);
        self::assertSame('int', $updatedPayload->fields[0]->phpdocType);
    }
}
