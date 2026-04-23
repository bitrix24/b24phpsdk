<?php

declare(strict_types=1);

namespace Bitrix24\SDK\Tests\Unit\OpenApi\Domain;

use Bitrix24\SDK\Infrastructure\Console\Commands\Metadata\Bitrix24MethodResultFetcher;
use Bitrix24\SDK\OpenApi\Domain\ResultItem\ResultItemPayload;
use Bitrix24\SDK\OpenApi\Domain\ResultItem\ResultItemPayloadField;
use Bitrix24\SDK\OpenApi\Domain\ResultItem\ResultItemPayloadSection;
use Bitrix24\SDK\OpenApi\Domain\ResultItem\ResultItemPayloadVerifier;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

final class ResultItemPayloadVerifierTest extends TestCase
{
    #[Test]
    public function itBuildsVerificationFindingsFromTheRuntimePayload(): void
    {
        $payload = new ResultItemPayload(
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
                    phpdocType: 'Carbon\\CarbonImmutable',
                    format: 'date-time',
                    required: true,
                    nullable: false,
                    source: 'b24restdocs',
                ),
                new ResultItemPayloadField(
                    code: 'title',
                    sourceType: 'string',
                    phpdocType: 'string',
                    format: null,
                    required: true,
                    nullable: false,
                    source: 'b24restdocs',
                ),
                new ResultItemPayloadField(
                    code: 'counter',
                    sourceType: 'integer',
                    phpdocType: 'int',
                    format: null,
                    required: true,
                    nullable: false,
                    source: 'openapi',
                ),
            ],
            sections: [
                new ResultItemPayloadSection(
                    name: 'restrictions',
                    kind: 'object',
                    source: 'b24restdocs',
                    fields: [
                        new ResultItemPayloadField(
                            code: 'avatar',
                            sourceType: 'boolean',
                            phpdocType: 'bool',
                            format: null,
                            required: true,
                            nullable: false,
                            source: 'b24restdocs',
                        ),
                    ],
                ),
            ],
        );

        $resultFetcher = $this->getMockBuilder(Bitrix24MethodResultFetcher::class)
            ->disableOriginalConstructor()
            ->onlyMethods(['fetch'])
            ->getMock();
        $resultFetcher
            ->expects(self::once())
            ->method('fetch')
            ->with('https://portal.example/rest/1/token/', 'im.dialog.get', ['DIALOG_ID' => 42])
            ->willReturn([
                'id' => 7,
                'date_create' => null,
                'counter' => '7',
                'status' => 'open',
                'permissions' => [
                    'can_write' => true,
                ],
                'restrictions' => [
                    'avatar' => true,
                ],
            ]);

        $report = (new ResultItemPayloadVerifier($resultFetcher))->verify(
            $payload,
            'https://portal.example/rest/1/token/',
            ['DIALOG_ID' => 42],
        );

        self::assertSame('im.dialog.get', $report->method);
        self::assertSame(
            [
                ['code' => 'id', 'section' => null],
                ['code' => 'avatar', 'section' => 'restrictions'],
            ],
            $report->confirmedFields,
        );
        self::assertSame(
            [
                ['code' => 'title', 'section' => null],
            ],
            $report->missingFields,
        );
        self::assertSame(
            [[
                'action' => 'add_field',
                'code' => 'status',
                'section' => null,
                'source_type' => 'string',
                'phpdoc_type' => 'string',
                'format' => null,
                'nullable' => false,
            ], [
                'action' => 'review_structural_addition',
                'code' => 'permissions',
                'section' => null,
                'source_type' => 'object',
                'phpdoc_type' => 'array',
                'format' => null,
                'nullable' => false,
            ]],
            $report->unexpectedFields,
        );
        self::assertSame(
            [[
                'action' => 'review_type_mismatch',
                'code' => 'counter',
                'section' => null,
                'expected_source_type' => 'integer',
                'actual_source_type' => 'string',
                'expected_phpdoc_type' => 'int',
                'actual_phpdoc_type' => 'string',
                'expected_format' => null,
                'actual_format' => null,
            ]],
            $report->typeMismatches,
        );
        self::assertSame(
            [[
                'action' => 'mark_nullable',
                'code' => 'date_create',
                'section' => null,
            ]],
            $report->nullabilityObservations,
        );
    }
}
