<?php

declare(strict_types=1);

namespace Bitrix24\SDK\Tests\Unit\OpenApi\Domain;

use Bitrix24\SDK\OpenApi\Domain\ResultItem\ResultItemPayload;
use Bitrix24\SDK\OpenApi\Domain\ResultItem\ResultItemPayloadField;
use Bitrix24\SDK\OpenApi\Domain\ResultItem\ResultItemPayloadSection;
use Bitrix24\SDK\OpenApi\Domain\ResultItem\ResultItemPayloadSerializer;
use InvalidArgumentException;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

final class ResultItemPayloadSerializerTest extends TestCase
{
    private const string FIXTURE = __DIR__ . '/Fixtures/result-item-payload.yaml';

    #[Test]
    public function itDecodesTheCanonicalYamlPayload(): void
    {
        $serializer = new ResultItemPayloadSerializer();
        $fixture = (string) file_get_contents(self::FIXTURE);

        $payload = $serializer->decode($fixture);

        self::assertSame('im.dialog.get', $payload->method);
        self::assertSame('result-item', $payload->object);
        self::assertSame(['openapi', 'b24restdocs'], $payload->generatedFrom);
        self::assertSame(1, $payload->version);
        self::assertCount(2, $payload->fields);
        self::assertCount(1, $payload->sections);

        self::assertSame('id', $payload->fields[0]->code);
        self::assertSame('restrictions', $payload->sections[0]->name);
        self::assertSame('avatar', $payload->sections[0]->fields[0]->code);

        self::assertSame($fixture, $serializer->encode($payload));
    }

    #[Test]
    public function itEncodesAndDecodesTheCanonicalYamlPayloadWithoutLosingStructure(): void
    {
        $serializer = new ResultItemPayloadSerializer();

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
                    description: 'Chat identifier',
                    notes: null,
                ),
                new ResultItemPayloadField(
                    code: 'date_create',
                    sourceType: 'datetime',
                    phpdocType: 'Carbon\\CarbonImmutable',
                    format: 'date-time',
                    required: true,
                    nullable: false,
                    source: 'b24restdocs',
                    description: 'Chat creation date in ATOM format',
                    notes: null,
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
                            description: 'Availability of avatar change',
                            notes: null,
                        ),
                    ],
                ),
            ],
        );

        $encoded = $serializer->encode($payload);
        $decoded = $serializer->decode($encoded);

        self::assertSame($payload->method, $decoded->method);
        self::assertSame($payload->object, $decoded->object);
        self::assertSame($payload->generatedFrom, $decoded->generatedFrom);
        self::assertSame($payload->version, $decoded->version);
        self::assertEquals($payload, $decoded);
    }

    #[Test]
    public function itRoundTripsMultilineNotesUsingEscapedNewlines(): void
    {
        $serializer = new ResultItemPayloadSerializer();

        $payload = new ResultItemPayload(
            method: 'im.dialog.get',
            object: 'result-item',
            generatedFrom: ['openapi'],
            fields: [
                new ResultItemPayloadField(
                    code: 'notes',
                    sourceType: 'string',
                    phpdocType: 'string',
                    format: null,
                    required: true,
                    nullable: false,
                    source: 'b24restdocs',
                    description: 'Multi-line notes',
                    notes: "First line\nSecond line",
                ),
            ],
            sections: [],
        );

        $encoded = $serializer->encode($payload);

        self::assertStringContainsString('notes: "First line\\nSecond line"', $encoded);
        self::assertSame("First line\nSecond line", $serializer->decode($encoded)->fields[0]->notes);
    }

    #[Test]
    public function itRoundTripsNumericLookingStringsAsStrings(): void
    {
        $serializer = new ResultItemPayloadSerializer();

        $payload = new ResultItemPayload(
            method: 'im.dialog.get',
            object: 'result-item',
            generatedFrom: ['openapi'],
            fields: [
                new ResultItemPayloadField(
                    code: 'code',
                    sourceType: 'string',
                    phpdocType: 'string',
                    format: null,
                    required: true,
                    nullable: false,
                    source: 'b24restdocs',
                    description: '1.5',
                    notes: '01',
                ),
            ],
            sections: [],
        );

        $encoded = $serializer->encode($payload);
        $decoded = $serializer->decode($encoded);

        self::assertStringContainsString('description: "1.5"', $encoded);
        self::assertStringContainsString('notes: "01"', $encoded);
        self::assertSame('1.5', $decoded->fields[0]->description);
        self::assertSame('01', $decoded->fields[0]->notes);
    }

    #[Test]
    public function itRoundTripsLiteralTildeAsAString(): void
    {
        $serializer = new ResultItemPayloadSerializer();

        $payload = new ResultItemPayload(
            method: 'im.dialog.get',
            object: 'result-item',
            generatedFrom: ['openapi'],
            fields: [
                new ResultItemPayloadField(
                    code: 'value',
                    sourceType: 'string',
                    phpdocType: 'string',
                    format: null,
                    required: true,
                    nullable: false,
                    source: 'b24restdocs',
                    description: '~',
                    notes: null,
                ),
            ],
            sections: [],
        );

        $encoded = $serializer->encode($payload);
        $decoded = $serializer->decode($encoded);

        self::assertStringContainsString('description: "~"', $encoded);
        self::assertSame('~', $decoded->fields[0]->description);
    }

    #[Test]
    public function itDecodesSingleQuotedStringsLiterally(): void
    {
        $serializer = new ResultItemPayloadSerializer();

        $payload = $serializer->decode(<<<'YAML'
version: 1
method: im.dialog.get
object: result-item
generated_from: []
fields:
  - code: path
    source_type: string
    phpdoc_type: string
    format: null
    required: true
    nullable: false
    source: b24restdocs
    description: 'C:\\tmp\\n'
    notes: null
sections: []
YAML);

        self::assertSame('C:\\\\tmp\\\\n', $payload->fields[0]->description);
    }

    #[Test]
    public function itRoundTripsDoubleQuotedBackslashSequencesLiterally(): void
    {
        $serializer = new ResultItemPayloadSerializer();

        $payload = new ResultItemPayload(
            method: 'im.dialog.get',
            object: 'result-item',
            generatedFrom: ['ignored'],
            fields: [
                new ResultItemPayloadField(
                    code: 'path',
                    sourceType: 'string',
                    phpdocType: 'string',
                    format: null,
                    required: true,
                    nullable: false,
                    source: 'b24restdocs',
                    description: 'C:\\tmp\\n folder',
                    notes: null,
                ),
            ],
            sections: [],
        );

        $encoded = $serializer->encode($payload);
        $decoded = $serializer->decode($encoded);

        self::assertStringContainsString('description: "C:\\\\tmp\\\\n folder"', $encoded);
        self::assertSame('C:\\tmp\\n folder', $decoded->fields[0]->description);
    }

    #[Test]
    public function itDecodesAnUnquotedColonBearingSequenceItemAsAScalar(): void
    {
        $serializer = new ResultItemPayloadSerializer();

        $payload = $serializer->decode(<<<'YAML'
version: 1
method: im.dialog.get
object: result-item
generated_from:
  - urn:foo
fields: []
sections: []
YAML);

        self::assertSame(['urn:foo'], $payload->generatedFrom);
    }

    #[Test]
    public function itRejectsEmptyMappingLiteralForListFields(): void
    {
        $serializer = new ResultItemPayloadSerializer();

        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Malformed payload: empty mapping scalar "{}" is not allowed.');

        $serializer->decode(<<<'YAML'
version: 1
method: im.dialog.get
object: result-item
generated_from: {}
fields: []
sections: []
YAML);
    }

    #[Test]
    public function itRejectsInvalidFieldBooleans(): void
    {
        $serializer = new ResultItemPayloadSerializer();

        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Invalid fields[0].required: expected true or false.');

        $serializer->decode(<<<'YAML'
version: 1
method: im.dialog.get
object: result-item
generated_from:
  - openapi
fields:
  - code: id
    source_type: integer
    phpdoc_type: int
    format: null
    required: nope
    nullable: false
    source: openapi
    description: Chat identifier
    notes: null
sections: []
YAML);
    }

    #[Test]
    public function itRejectsMissingRequiredTopLevelKeys(): void
    {
        $serializer = new ResultItemPayloadSerializer();

        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Missing required key "method" in payload.');

        $serializer->decode(<<<'YAML'
version: 1
object: result-item
generated_from:
  - openapi
fields: []
sections: []
YAML);
    }

    #[Test]
    public function itRejectsTrailingGarbageAfterAValidPayload(): void
    {
        $serializer = new ResultItemPayloadSerializer();
        $fixture = (string) file_get_contents(self::FIXTURE);

        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Unexpected key "trailing" in payload.');

        $serializer->decode($fixture . "\ntrailing: garbage\n");
    }

    #[Test]
    public function itRejectsDuplicateTopLevelKeys(): void
    {
        $serializer = new ResultItemPayloadSerializer();

        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Duplicate key "method" in payload near line 4.');

        $serializer->decode(<<<'YAML'
version: 1
method: im.dialog.get

method: duplicated
object: result-item
generated_from:
  - openapi
fields: []
sections: []
YAML);
    }

    #[Test]
    public function itRejectsDuplicateKeysInSequenceTailAtTheCorrectLine(): void
    {
        $serializer = new ResultItemPayloadSerializer();

        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Duplicate key "name" in payload.sections[0] near line 20.');

        $serializer->decode(<<<'YAML'
version: 1
method: im.dialog.get
object: result-item
generated_from:
  - openapi
fields: []
sections:
  - fields:
      - code: avatar
        source_type: boolean
        phpdoc_type: bool
        format: null
        required: true
        nullable: false
        source: b24restdocs
        description: Availability of avatar change
        notes: null

    name: restrictions
    name: duplicate
    kind: object
    source: b24restdocs
YAML);
    }
}
