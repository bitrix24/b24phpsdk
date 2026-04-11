<?php

/**
 * This file is part of the bitrix24-php-sdk package.
 *
 * © Maksim Mesilov <mesilov.maxim@gmail.com>
 *
 * For the full copyright and license information, please view the MIT-LICENSE.txt
 * file that was distributed with this source code.
 */

declare(strict_types=1);

namespace Bitrix24\SDK\Tests\Unit\OpenApi\Domain;

use Bitrix24\SDK\OpenApi\Domain\OpenApiSchemaEntityReader;
use Bitrix24\SDK\OpenApi\Domain\V3BuilderCoverageAuditor;
use Bitrix24\SDK\OpenApi\Domain\V3BuilderCoverageReport;
use Bitrix24\SDK\Tests\Unit\OpenApi\Domain\Fixtures\DuplicateEntityKeyResult1;
use Bitrix24\SDK\Tests\Unit\OpenApi\Domain\Fixtures\DuplicateEntityKeyResult2;
use Bitrix24\SDK\Tests\Unit\OpenApi\Domain\Fixtures\FullCoverageResult;
use Bitrix24\SDK\Tests\Unit\OpenApi\Domain\Fixtures\MissingItemBuilderResult;
use Bitrix24\SDK\Tests\Unit\OpenApi\Domain\Fixtures\MissingSelectBuilderResult;
use Bitrix24\SDK\Tests\Unit\OpenApi\Domain\Fixtures\NonExistentItemBuilderResult;
use Bitrix24\SDK\Tests\Unit\OpenApi\Domain\Fixtures\NonExistentSelectBuilderResult;
use Bitrix24\SDK\Tests\Unit\OpenApi\Domain\Fixtures\PartialCoverageResult;
use Bitrix24\SDK\Tests\Unit\OpenApi\Domain\Fixtures\UnknownEntityKeyResult;
use Bitrix24\SDK\Tests\Unit\OpenApi\Domain\Fixtures\WrongSelectBuilderTypeResult;
use Generator;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Filesystem\Filesystem;

#[CoversClass(V3BuilderCoverageAuditor::class)]
class V3BuilderCoverageAuditorTest extends TestCase
{
    private V3BuilderCoverageAuditor $auditor;

    private string $schemaFile;

    #[\Override]
    protected function setUp(): void
    {
        $this->auditor = new V3BuilderCoverageAuditor(new OpenApiSchemaEntityReader(new Filesystem()));
        $this->schemaFile = sys_get_temp_dir() . '/test_openapi_' . uniqid() . '.json';
    }

    #[\Override]
    protected function tearDown(): void
    {
        @unlink($this->schemaFile);
    }

    /**
     * @param list<string>                            $entityKeys    entity keys to include in the temp schema
     * @param list<class-string>                      $sdkClassNames classes to pass to the auditor
     * @param callable(V3BuilderCoverageReport): void $assertions
     * @param array<string, mixed>|null               $customSchema  full schema content override (when simple entity keys are not enough)
     */
    #[Test]
    #[DataProvider('auditScenariosProvider')]
    public function testAudit(array $entityKeys, array $sdkClassNames, callable $assertions, ?array $customSchema = null): void
    {
        if ($customSchema !== null) {
            file_put_contents($this->schemaFile, json_encode($customSchema, JSON_THROW_ON_ERROR));
        } else {
            $this->writeSchema($entityKeys);
        }

        $v3BuilderCoverageReport = $this->auditor->audit($this->schemaFile, $sdkClassNames);
        $assertions($v3BuilderCoverageReport);
    }

    public static function auditScenariosProvider(): Generator
    {
        yield 'all entities mapped and valid — zero issues' => [
            ['test.fixture.fulldto'],
            [FullCoverageResult::class],
            static function (V3BuilderCoverageReport $v3BuilderCoverageReport): void {
                self::assertSame(1, $v3BuilderCoverageReport->totalOpenApiEntities);
                self::assertSame(1, $v3BuilderCoverageReport->mappedEntities);
                self::assertSame([], $v3BuilderCoverageReport->unmappedEntities);
                self::assertSame([], $v3BuilderCoverageReport->missingSelectBuilders);
                self::assertSame([], $v3BuilderCoverageReport->missingItemBuilders);
                self::assertSame([], $v3BuilderCoverageReport->invalidBuilderReferences);
                self::assertSame([], $v3BuilderCoverageReport->selectCoverageMismatches);
                self::assertSame([], $v3BuilderCoverageReport->sdkOnlyMappings);
                self::assertSame([], $v3BuilderCoverageReport->duplicateEntityKeyMappings);
            },
        ];

        yield 'DTO in snapshot without SDK mapping — appears in unmappedEntities' => [
            ['test.fixture.unmappeddto'],
            [],
            static function (V3BuilderCoverageReport $v3BuilderCoverageReport): void {
                self::assertSame(['test.fixture.unmappeddto'], $v3BuilderCoverageReport->unmappedEntities);
                self::assertSame(0, $v3BuilderCoverageReport->mappedEntities);
            },
        ];

        yield 'mapping present, selectBuilder is null — appears in missingSelectBuilders' => [
            ['test.fixture.missingselectdto'],
            [MissingSelectBuilderResult::class],
            static function (V3BuilderCoverageReport $v3BuilderCoverageReport): void {
                self::assertContains('test.fixture.missingselectdto', $v3BuilderCoverageReport->missingSelectBuilders);
                self::assertSame([], $v3BuilderCoverageReport->invalidBuilderReferences);
            },
        ];

        yield 'mapping present, itemBuilder is null — appears in missingItemBuilders' => [
            ['test.fixture.missingitemdto'],
            [MissingItemBuilderResult::class],
            static function (V3BuilderCoverageReport $v3BuilderCoverageReport): void {
                self::assertContains('test.fixture.missingitemdto', $v3BuilderCoverageReport->missingItemBuilders);
                self::assertSame([], $v3BuilderCoverageReport->invalidBuilderReferences);
            },
        ];

        yield 'selectBuilder class does not exist — appears in invalidBuilderReferences' => [
            ['test.fixture.nonexistentselectdto'],
            [NonExistentSelectBuilderResult::class],
            static function (V3BuilderCoverageReport $v3BuilderCoverageReport): void {
                self::assertCount(1, $v3BuilderCoverageReport->invalidBuilderReferences);
                self::assertSame('test.fixture.nonexistentselectdto', $v3BuilderCoverageReport->invalidBuilderReferences[0]['entityKey']);
                self::assertSame('class does not exist', $v3BuilderCoverageReport->invalidBuilderReferences[0]['reason']);
            },
        ];

        yield 'itemBuilder class does not exist — appears in invalidBuilderReferences' => [
            ['test.fixture.nonexistentitemdto'],
            [NonExistentItemBuilderResult::class],
            static function (V3BuilderCoverageReport $v3BuilderCoverageReport): void {
                self::assertCount(1, $v3BuilderCoverageReport->invalidBuilderReferences);
                self::assertSame('test.fixture.nonexistentitemdto', $v3BuilderCoverageReport->invalidBuilderReferences[0]['entityKey']);
                self::assertSame('class does not exist', $v3BuilderCoverageReport->invalidBuilderReferences[0]['reason']);
            },
        ];

        yield 'selectBuilder does not extend AbstractSelectBuilder — appears in invalidBuilderReferences' => [
            ['test.fixture.wrongselecttypedto'],
            [WrongSelectBuilderTypeResult::class],
            static function (V3BuilderCoverageReport $v3BuilderCoverageReport): void {
                self::assertCount(1, $v3BuilderCoverageReport->invalidBuilderReferences);
                self::assertSame('test.fixture.wrongselecttypedto', $v3BuilderCoverageReport->invalidBuilderReferences[0]['entityKey']);
                self::assertStringContainsString('does not extend', $v3BuilderCoverageReport->invalidBuilderReferences[0]['reason']);
            },
        ];

        yield 'selectBuilder missing OpenAPI fields — appears in selectCoverageMismatches' => [
            ['test.fixture.partialcoveragedto'],
            [PartialCoverageResult::class],
            static function (V3BuilderCoverageReport $v3BuilderCoverageReport): void {
                self::assertCount(1, $v3BuilderCoverageReport->selectCoverageMismatches);
                self::assertSame('test.fixture.partialcoveragedto', $v3BuilderCoverageReport->selectCoverageMismatches[0]['entityKey']);
                self::assertContains('title', $v3BuilderCoverageReport->selectCoverageMismatches[0]['missingFields']);
            },
        ];

        yield '#[OpenApiEntity] points to unknown entityKey — appears in sdkOnlyMappings' => [
            [],
            [UnknownEntityKeyResult::class],
            static function (V3BuilderCoverageReport $v3BuilderCoverageReport): void {
                self::assertCount(1, $v3BuilderCoverageReport->sdkOnlyMappings);
                self::assertSame('unknown.entity.thisdoesnotexist', $v3BuilderCoverageReport->sdkOnlyMappings[0]['entityKey']);
            },
        ];

        yield 'sub-entity referenced via $ref is excluded from unmapped' => [
            [],  // unused when customSchema is provided
            [FullCoverageResult::class],
            static function (V3BuilderCoverageReport $v3BuilderCoverageReport): void {
                // 'test.fixture.subdto' is a $ref target inside 'test.fixture.fulldto',
                // so it must NOT appear in unmappedEntities (it is a nested sub-type)
                self::assertNotContains('test.fixture.subdto', $v3BuilderCoverageReport->unmappedEntities);
                self::assertSame(1, $v3BuilderCoverageReport->totalOpenApiEntities, 'only root entity counts');
            },
            [
                'components' => [
                    'schemas' => [
                        'test.fixture.fulldto' => [
                            'type' => 'object',
                            'properties' => [
                                'id' => ['type' => 'integer'],
                                'title' => ['type' => 'string'],
                                'sub' => ['$ref' => '#/components/schemas/test.fixture.subdto'],
                            ],
                        ],
                        'test.fixture.subdto' => [
                            'type' => 'object',
                            'properties' => [
                                'id' => ['type' => 'integer'],
                                'name' => ['type' => 'string'],
                            ],
                        ],
                    ],
                ],
            ],
        ];

        yield 'entity from different module prefix not included in unmapped' => [
            ['test.fixture.fulldto', 'other.module.unrelatedto'],
            [FullCoverageResult::class],
            static function (V3BuilderCoverageReport $v3BuilderCoverageReport): void {
                // 'other.module.unrelatedto' must NOT appear — it is outside prefix 'test.fixture'
                self::assertNotContains('other.module.unrelatedto', $v3BuilderCoverageReport->unmappedEntities);
                // totalOpenApiEntities must reflect only the filtered set
                self::assertSame(1, $v3BuilderCoverageReport->totalOpenApiEntities);
            },
        ];

        yield 'orphaned DTO not referenced in any API path — excluded from totalOpenApiEntities' => [
            [],  // unused when customSchema is provided
            [],
            static function (V3BuilderCoverageReport $v3BuilderCoverageReport): void {
                // 'test.fixture.orphandto' is defined in schema but never referenced in any
                // API path ($ref), so it is excluded — not counted, not unmapped
                self::assertNotContains('test.fixture.orphandto', $v3BuilderCoverageReport->unmappedEntities);
                self::assertSame(0, $v3BuilderCoverageReport->totalOpenApiEntities);
            },
            [
                'paths' => [],
                'components' => [
                    'schemas' => [
                        'test.fixture.orphandto' => [
                            'type' => 'object',
                            'properties' => [
                                'id' => ['type' => 'integer'],
                                'name' => ['type' => 'string'],
                            ],
                        ],
                    ],
                ],
            ],
        ];

        yield 'two result classes with same entityKey — appears in duplicateEntityKeyMappings' => [
            ['test.fixture.duplicatedto'],
            [DuplicateEntityKeyResult1::class, DuplicateEntityKeyResult2::class],
            static function (V3BuilderCoverageReport $v3BuilderCoverageReport): void {
                self::assertCount(1, $v3BuilderCoverageReport->duplicateEntityKeyMappings);
                self::assertSame('test.fixture.duplicatedto', $v3BuilderCoverageReport->duplicateEntityKeyMappings[0]['entityKey']);
                self::assertCount(2, $v3BuilderCoverageReport->duplicateEntityKeyMappings[0]['resultClasses']);
                self::assertSame([], $v3BuilderCoverageReport->invalidBuilderReferences);
            },
        ];
    }

    /**
     * Writes a minimal OpenAPI schema with both components/schemas and paths sections.
     * Each entity key gets a fake GET endpoint that references it, so the auditor's
     * orphaned-DTO filter does not accidentally exclude test entities.
     *
     * @param list<string> $entityKeys
     */
    private function writeSchema(array $entityKeys): void
    {
        $schemas = [];
        $paths = [];
        foreach ($entityKeys as $entityKey) {
            $schemas[$entityKey] = [
                'type' => 'object',
                'properties' => [
                    'id' => ['type' => 'integer'],
                    'title' => ['type' => 'string'],
                ],
            ];
            $paths['/fake/' . str_replace('.', '-', $entityKey)] = [
                'get' => [
                    'responses' => [
                        '200' => [
                            'content' => [
                                'application/json' => [
                                    'schema' => ['$ref' => '#/components/schemas/' . $entityKey],
                                ],
                            ],
                        ],
                    ],
                ],
            ];
        }

        file_put_contents(
            $this->schemaFile,
            json_encode(['paths' => $paths, 'components' => ['schemas' => $schemas]], JSON_THROW_ON_ERROR)
        );
    }
}
