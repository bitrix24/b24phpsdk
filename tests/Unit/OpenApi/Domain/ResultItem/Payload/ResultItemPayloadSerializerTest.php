<?php

declare(strict_types=1);

namespace Bitrix24\SDK\Tests\Unit\OpenApi\Domain\ResultItem\Payload;

use Bitrix24\SDK\OpenApi\Domain\ResultItem\Payload\ResultItemPayload;
use Bitrix24\SDK\OpenApi\Domain\ResultItem\Payload\ResultItemPayloadField;
use Bitrix24\SDK\OpenApi\Domain\ResultItem\Payload\ResultItemPayloadSection;
use Bitrix24\SDK\OpenApi\Domain\ResultItem\Payload\ResultItemPayloadSerializer;
use InvalidArgumentException;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

final class ResultItemPayloadSerializerTest extends TestCase
{
    private const string FIXTURE = __DIR__ . '/../Fixtures/result-item-payload.yaml';

    #[Test]
    public function itDecodesTheCanonicalYamlPayload(): void
    {
        $resultItemPayloadSerializer = new ResultItemPayloadSerializer();
        $fixture = (string) file_get_contents(self::FIXTURE);

        $resultItemPayload = $resultItemPayloadSerializer->decode($fixture);

        self::assertSame('im.dialog.get', $resultItemPayload->method);
        self::assertSame('result-item', $resultItemPayload->object);
        self::assertSame(['openapi', 'b24restdocs'], $resultItemPayload->generatedFrom);
        self::assertSame(1, $resultItemPayload->version);
        self::assertCount(2, $resultItemPayload->fields);
        self::assertCount(1, $resultItemPayload->sections);

        self::assertSame('id', $resultItemPayload->fields[0]->code);
        self::assertSame('restrictions', $resultItemPayload->sections[0]->name);
        self::assertSame('avatar', $resultItemPayload->sections[0]->fields[0]->code);

        self::assertSame($fixture, $resultItemPayloadSerializer->encode($resultItemPayload));
    }

    #[Test]
    public function itEncodesAndDecodesTheCanonicalYamlPayloadWithoutLosingStructure(): void
    {
        $resultItemPayloadSerializer = new ResultItemPayloadSerializer();

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
                    description: 'Chat identifier',
                    notes: null,
                ),
                new ResultItemPayloadField(
                    code: 'date_create',
                    sourceType: 'datetime',
                    phpdocType: \Carbon\CarbonImmutable::class,
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

        $encoded = $resultItemPayloadSerializer->encode($resultItemPayload);
        $decoded = $resultItemPayloadSerializer->decode($encoded);

        self::assertSame($resultItemPayload->method, $decoded->method);
        self::assertSame($resultItemPayload->object, $decoded->object);
        self::assertSame($resultItemPayload->generatedFrom, $decoded->generatedFrom);
        self::assertSame($resultItemPayload->version, $decoded->version);
        self::assertEquals($resultItemPayload, $decoded);
    }

    #[Test]
    public function itRoundTripsMultilineNotesUsingEscapedNewlines(): void
    {
        $resultItemPayloadSerializer = new ResultItemPayloadSerializer();

        $resultItemPayload = new ResultItemPayload(
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

        $encoded = $resultItemPayloadSerializer->encode($resultItemPayload);

        self::assertStringContainsString('notes: "First line\\nSecond line"', $encoded);
        self::assertSame("First line\nSecond line", $resultItemPayloadSerializer->decode($encoded)->fields[0]->notes);
    }

    #[Test]
    public function itRoundTripsNumericLookingStringsAsStrings(): void
    {
        $resultItemPayloadSerializer = new ResultItemPayloadSerializer();

        $resultItemPayload = new ResultItemPayload(
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

        $encoded = $resultItemPayloadSerializer->encode($resultItemPayload);
        $decoded = $resultItemPayloadSerializer->decode($encoded);

        self::assertStringContainsString('description: "1.5"', $encoded);
        self::assertStringContainsString('notes: "01"', $encoded);
        self::assertSame('1.5', $decoded->fields[0]->description);
        self::assertSame('01', $decoded->fields[0]->notes);
    }

    #[Test]
    public function itRoundTripsLiteralTildeAsAString(): void
    {
        $resultItemPayloadSerializer = new ResultItemPayloadSerializer();

        $resultItemPayload = new ResultItemPayload(
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

        $encoded = $resultItemPayloadSerializer->encode($resultItemPayload);
        $decoded = $resultItemPayloadSerializer->decode($encoded);

        self::assertStringContainsString('description: "~"', $encoded);
        self::assertSame('~', $decoded->fields[0]->description);
    }

    #[Test]
    public function itDecodesSingleQuotedStringsLiterally(): void
    {
        $resultItemPayloadSerializer = new ResultItemPayloadSerializer();

        $resultItemPayload = $resultItemPayloadSerializer->decode(<<<'YAML'
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

        self::assertSame('C:\\\\tmp\\\\n', $resultItemPayload->fields[0]->description);
    }

    #[Test]
    public function itRoundTripsDoubleQuotedBackslashSequencesLiterally(): void
    {
        $resultItemPayloadSerializer = new ResultItemPayloadSerializer();

        $resultItemPayload = new ResultItemPayload(
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

        $encoded = $resultItemPayloadSerializer->encode($resultItemPayload);
        $decoded = $resultItemPayloadSerializer->decode($encoded);

        self::assertStringContainsString('description: "C:\\\\tmp\\\\n folder"', $encoded);
        self::assertSame('C:\\tmp\\n folder', $decoded->fields[0]->description);
    }

    #[Test]
    public function itDecodesAnUnquotedColonBearingSequenceItemAsAScalar(): void
    {
        $resultItemPayloadSerializer = new ResultItemPayloadSerializer();

        $resultItemPayload = $resultItemPayloadSerializer->decode(<<<'YAML'
version: 1
method: im.dialog.get
object: result-item
generated_from:
  - urn:foo
fields: []
sections: []
YAML);

        self::assertSame(['urn:foo'], $resultItemPayload->generatedFrom);
    }

    #[Test]
    public function itRejectsEmptyMappingLiteralForListFields(): void
    {
        $resultItemPayloadSerializer = new ResultItemPayloadSerializer();

        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Malformed payload: empty mapping scalar "{}" is not allowed.');

        $resultItemPayloadSerializer->decode(<<<'YAML'
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
        $resultItemPayloadSerializer = new ResultItemPayloadSerializer();

        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Invalid fields[0].required: expected true or false.');

        $resultItemPayloadSerializer->decode(<<<'YAML'
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
        $resultItemPayloadSerializer = new ResultItemPayloadSerializer();

        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Missing required key "method" in payload.');

        $resultItemPayloadSerializer->decode(<<<'YAML'
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
        $resultItemPayloadSerializer = new ResultItemPayloadSerializer();
        $fixture = (string) file_get_contents(self::FIXTURE);

        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Unexpected key "trailing" in payload.');

        $resultItemPayloadSerializer->decode($fixture . "\ntrailing: garbage\n");
    }

    #[Test]
    public function itRejectsDuplicateTopLevelKeys(): void
    {
        $resultItemPayloadSerializer = new ResultItemPayloadSerializer();

        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Duplicate key "method" in payload near line 4.');

        $resultItemPayloadSerializer->decode(<<<'YAML'
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
        $resultItemPayloadSerializer = new ResultItemPayloadSerializer();

        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Duplicate key "name" in payload.sections[0] near line 20.');

        $resultItemPayloadSerializer->decode(<<<'YAML'
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
