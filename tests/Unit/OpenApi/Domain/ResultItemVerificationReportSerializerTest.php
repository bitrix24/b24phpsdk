<?php

declare(strict_types=1);

namespace Bitrix24\SDK\Tests\Unit\OpenApi\Domain;

use Bitrix24\SDK\OpenApi\Domain\ResultItem\ResultItemVerificationReport;
use Bitrix24\SDK\OpenApi\Domain\ResultItem\ResultItemVerificationReportSerializer;
use InvalidArgumentException;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

final class ResultItemVerificationReportSerializerTest extends TestCase
{
    #[Test]
    public function itRoundTripsReportEntries(): void
    {
        $serializer = new ResultItemVerificationReportSerializer();
        $report = new ResultItemVerificationReport(
            method: 'im.dialog.get',
            confirmedFields: [['code' => 'id', 'section' => null]],
            missingFields: [],
            unexpectedFields: [[
                'action' => 'add_field',
                'code' => 'status',
                'section' => null,
                'source_type' => 'string',
                'phpdoc_type' => 'string|null',
                'format' => null,
                'nullable' => true,
            ]],
            typeMismatches: [],
            nullabilityObservations: [[
                'action' => 'mark_nullable',
                'code' => 'date_create',
                'section' => null,
            ]],
        );

        self::assertEquals($report, $serializer->decode($serializer->encode($report)));
    }

    #[Test]
    public function itRejectsDuplicateTopLevelKeys(): void
    {
        $serializer = new ResultItemVerificationReportSerializer();

        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Duplicate key "method" in verification report near line 2.');

        $serializer->decode(<<<'YAML'
method: im.dialog.get
method: im.dialog.list
confirmed_fields: []
missing_fields: []
unexpected_fields: []
type_mismatches: []
nullability_observations: []
YAML);
    }

    #[Test]
    public function itRejectsDuplicateKeysInsideListEntries(): void
    {
        $serializer = new ResultItemVerificationReportSerializer();

        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Duplicate key "code" in verification report.unexpected_fields[0] near line 6.');

        $serializer->decode(<<<'YAML'
method: im.dialog.get
confirmed_fields: []
missing_fields: []
unexpected_fields:
  - code: status
    code: duplicate
    action: add_field
    section: null
    source_type: string
    phpdoc_type: string
    format: null
    nullable: false
type_mismatches: []
nullability_observations: []
YAML);
    }

    #[Test]
    public function itRejectsEncodingEmptyEntryArrays(): void
    {
        $serializer = new ResultItemVerificationReportSerializer();

        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Verification report entries must not be empty arrays.');

        $serializer->encode(new ResultItemVerificationReport(
            method: 'im.dialog.get',
            confirmedFields: [[]],
            missingFields: [],
            unexpectedFields: [],
            typeMismatches: [],
            nullabilityObservations: [],
        ));
    }
}
