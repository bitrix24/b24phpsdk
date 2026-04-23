<?php

declare(strict_types=1);

namespace Bitrix24\SDK\Tests\Unit\OpenApi\Domain;

use Bitrix24\SDK\OpenApi\Domain\ResultItem\ResultItemPayload;
use Bitrix24\SDK\OpenApi\Domain\ResultItem\ResultItemPayloadBuilder;
use Bitrix24\SDK\OpenApi\Domain\ResultItem\ResultItemPayloadField;
use Bitrix24\SDK\OpenApi\Domain\ResultItem\ResultItemPayloadSection;
use InvalidArgumentException;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

final class ResultItemPayloadBuilderTest extends TestCase
{
    #[Test]
    public function itMergesOpenApiAndDocsPayloadsWithOpenApiPrecedence(): void
    {
        $builder = new ResultItemPayloadBuilder();

        $payload = $builder->build(
            new ResultItemPayload(
                method: 'im.dialog.get',
                object: 'result-item',
                generatedFrom: ['openapi'],
                fields: [
                    new ResultItemPayloadField(
                        code: 'date_create',
                        sourceType: 'datetime',
                        phpdocType: 'Carbon\\CarbonImmutable',
                        format: 'date-time',
                        required: true,
                        nullable: false,
                        source: 'openapi',
                        description: null,
                        notes: null,
                    ),
                    new ResultItemPayloadField(
                        code: 'background_id',
                        sourceType: 'integer',
                        phpdocType: 'int',
                        format: null,
                        required: true,
                        nullable: false,
                        source: 'openapi',
                        description: null,
                        notes: null,
                    ),
                ],
                sections: [],
                version: 3,
            ),
            new ResultItemPayload(
                method: 'im.dialog.get',
                object: 'result-item',
                generatedFrom: ['b24restdocs'],
                fields: [
                    new ResultItemPayloadField(
                        code: 'date_create',
                        sourceType: 'datetime',
                        phpdocType: 'Carbon\\CarbonImmutable',
                        format: 'date-time',
                        required: true,
                        nullable: false,
                        source: 'b24restdocs',
                        description: 'Chat creation date in ATOM format',
                        notes: 'docs note',
                    ),
                    new ResultItemPayloadField(
                        code: 'background_id',
                        sourceType: 'integer',
                        phpdocType: 'int|null',
                        format: null,
                        required: false,
                        nullable: true,
                        source: 'b24restdocs',
                        description: 'Identifier of the chat background',
                        notes: 'If not specified, the value is `null`',
                    ),
                ],
                sections: [
                    new ResultItemPayloadSection(
                        name: 'permissions',
                        kind: 'object',
                        source: 'b24restdocs',
                        fields: [
                            new ResultItemPayloadField(
                                code: 'can_post',
                                sourceType: 'string',
                                phpdocType: 'string',
                                format: null,
                                required: true,
                                nullable: false,
                                source: 'b24restdocs',
                                description: 'Permission to send messages',
                                notes: null,
                            ),
                        ],
                    ),
                ],
                version: 7,
            ),
        );

        self::assertSame(['openapi', 'b24restdocs'], $payload->generatedFrom);
        self::assertSame(3, $payload->version);
        self::assertCount(2, $payload->fields);
        self::assertCount(1, $payload->sections);

        $dateCreate = $this->findField($payload->fields, 'date_create');
        self::assertNotNull($dateCreate);
        self::assertSame('datetime', $dateCreate->sourceType);
        self::assertTrue($dateCreate->required);
        self::assertFalse($dateCreate->nullable);
        self::assertSame('Chat creation date in ATOM format', $dateCreate->description);
        self::assertSame('docs note', $dateCreate->notes);

        $backgroundId = $this->findField($payload->fields, 'background_id');
        self::assertNotNull($backgroundId);
        self::assertSame('integer', $backgroundId->sourceType);
        self::assertTrue($backgroundId->required);
        self::assertFalse($backgroundId->nullable);
        self::assertSame('int', $backgroundId->phpdocType);
        self::assertSame('Identifier of the chat background', $backgroundId->description);
        self::assertStringContainsString('docs required=false, nullable=true', (string) $backgroundId->notes);

        $formatFromDocs = $builder->build(
            new ResultItemPayload(
                method: 'im.dialog.get',
                object: 'result-item',
                generatedFrom: ['openapi'],
                fields: [
                    new ResultItemPayloadField(
                        code: 'date_create',
                        sourceType: 'string',
                        phpdocType: 'string',
                        format: 'date',
                        required: true,
                        nullable: false,
                        source: 'openapi',
                        description: 'OpenAPI description',
                        notes: 'openapi note',
                    ),
                ],
                sections: [],
            ),
            new ResultItemPayload(
                method: 'im.dialog.get',
                object: 'result-item',
                generatedFrom: ['b24restdocs'],
                fields: [
                    new ResultItemPayloadField(
                        code: 'date_create',
                        sourceType: 'datetime',
                        phpdocType: 'Carbon\\CarbonImmutable',
                        format: 'date-time',
                        required: true,
                        nullable: false,
                        source: 'b24restdocs',
                        description: 'Docs description',
                        notes: 'docs note',
                    ),
                ],
                sections: [],
            ),
        );

        $docsSupplementedField = $this->findField($formatFromDocs->fields, 'date_create');
        self::assertNotNull($docsSupplementedField);
        self::assertSame('string', $docsSupplementedField->sourceType);
        self::assertSame('string', $docsSupplementedField->phpdocType);
        self::assertSame('date-time', $docsSupplementedField->format);
        self::assertSame('Docs description', $docsSupplementedField->description);
        self::assertStringContainsString('openapi note', (string) $docsSupplementedField->notes);
        self::assertStringContainsString('docs note', (string) $docsSupplementedField->notes);
        self::assertStringContainsString('docs description="Docs description", openapi description="OpenAPI description"', (string) $docsSupplementedField->notes);
        self::assertStringContainsString('docs format=date-time, openapi format=date', (string) $docsSupplementedField->notes);

        self::assertSame('permissions', $payload->sections[0]->name);
        self::assertSame('can_post', $payload->sections[0]->fields[0]->code);
    }

    #[Test]
    public function itReturnsTheOnlyAvailablePayloadUnchangedExceptForNormalizedGeneratedFrom(): void
    {
        $builder = new ResultItemPayloadBuilder();
        $docsPayload = new ResultItemPayload(
            method: 'im.dialog.get',
            object: 'result-item',
            generatedFrom: ['b24restdocs'],
            fields: [],
            sections: [],
        );

        $payload = $builder->build(null, $docsPayload);

        self::assertEquals($docsPayload, $payload);
    }

    #[Test]
    public function itFailsFastWhenPayloadMethodOrObjectDoNotMatch(): void
    {
        $builder = new ResultItemPayloadBuilder();

        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Cannot merge payloads with different method/object identity');

        $builder->build(
            new ResultItemPayload(
                method: 'im.dialog.get',
                object: 'result-item',
                generatedFrom: ['openapi'],
                fields: [],
                sections: [],
            ),
            new ResultItemPayload(
                method: 'im.dialog.list',
                object: 'result-list',
                generatedFrom: ['b24restdocs'],
                fields: [],
                sections: [],
            ),
        );
    }

    /**
     * @param list<\Bitrix24\SDK\OpenApi\Domain\ResultItem\ResultItemPayloadField> $fields
     */
    private function findField(array $fields, string $code): ?\Bitrix24\SDK\OpenApi\Domain\ResultItem\ResultItemPayloadField
    {
        foreach ($fields as $field) {
            if ($field->code === $code) {
                return $field;
            }
        }

        return null;
    }
}
