<?php

declare(strict_types=1);

namespace Bitrix24\SDK\Tests\Unit\OpenApi\Domain\ResultItem;

use Bitrix24\SDK\CodeGenerator\ResultItemCodeGenerator;
use Bitrix24\SDK\OpenApi\Domain\ResultItem\Payload\ResultItemPayloadSerializer;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

final class ResultItemCodeGeneratorFromPayloadTest extends TestCase
{
    private const string PAYLOAD_FIXTURE = __DIR__ . '/Fixtures/result-item-codegen-payload.yaml';

    #[Test]
    public function itGeneratesResultItemCodeFromTheCanonicalPayload(): void
    {
        $resultItemPayload = (new ResultItemPayloadSerializer())->decode((string) file_get_contents(self::PAYLOAD_FIXTURE));
        $resultItemCodeGenerator = new ResultItemCodeGenerator();

        $definitions = $resultItemCodeGenerator->buildPropertyDefinitionsFromPayload($resultItemPayload);
        $code = $resultItemCodeGenerator->generateFromPayload(
            'Bitrix24\\SDK\\Services\\IM\\Dialog\\Result',
            'DialogItemResult',
            $resultItemPayload,
        );

        self::assertSame([
            'name' => 'description',
            'phpType' => 'string',
            'required' => false,
            'nullable' => false,
            'description' => 'Dialog description text',
        ], $this->findDefinition($definitions, 'description'));

        self::assertSame([
            'name' => 'background_id',
            'phpType' => 'int|null',
            'required' => false,
            'nullable' => true,
            'description' => 'Identifier of the chat background',
        ], $this->findDefinition($definitions, 'background_id'));
        self::assertNull($this->findDefinition($definitions, 'restrictions'));

        self::assertStringContainsString('// Source: payload', $code);
        self::assertStringContainsString('use Carbon\CarbonImmutable;', $code);
        self::assertStringContainsString('@property-read CarbonImmutable $date_create', $code);
        self::assertStringContainsString('@property-read string $description', $code);
        self::assertStringContainsString('@property-read int|null $background_id', $code);
        self::assertStringNotContainsString('@property-read array $restrictions', $code);
        self::assertStringNotContainsString('@property-read array $readed_list_item', $code);
        self::assertStringContainsString('class DialogItemResult extends AbstractItem', $code);
    }

    /**
     * @param list<array{name: string, phpType: string, required: bool, nullable: bool, description: string|null}> $definitions
     * @return array{name: string, phpType: string, required: bool, nullable: bool, description: string|null}|null
     */
    private function findDefinition(array $definitions, string $name): ?array
    {
        foreach ($definitions as $definition) {
            if ($definition['name'] === $name) {
                return $definition;
            }
        }

        return null;
    }
}
