<?php

declare(strict_types=1);

namespace Bitrix24\SDK\Tests\Unit\OpenApi\Domain\ResultItem\Provider;

use Bitrix24\SDK\OpenApi\Domain\ResultItem\Provider\RestDocsResultItemPayloadProvider;
use Carbon\CarbonImmutable;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use RuntimeException;

final class RestDocsResultItemPayloadProviderTest extends TestCase
{
    private const string DOCS_FIXTURE = __DIR__ . '/../Fixtures/im-dialog-get.md';

    #[Test]
    public function itBuildsResultItemPayloadFromMarkdownDocumentation(): void
    {
        $restDocsResultItemPayloadProvider = new RestDocsResultItemPayloadProvider();

        $resultItemPayload = $restDocsResultItemPayloadProvider->provide(
            markdownFile: self::DOCS_FIXTURE,
            method: 'im.dialog.get',
        );

        self::assertSame('im.dialog.get', $resultItemPayload->method);
        self::assertSame('result-item', $resultItemPayload->object);
        self::assertSame(['b24restdocs'], $resultItemPayload->generatedFrom);

        $dateCreate = $this->findField($resultItemPayload->fields, 'date_create');
        self::assertNotNull($dateCreate);
        self::assertSame('datetime', $dateCreate->sourceType);
        self::assertSame(CarbonImmutable::class, $dateCreate->phpdocType);
        self::assertSame('date-time', $dateCreate->format);
        self::assertTrue($dateCreate->required);
        self::assertFalse($dateCreate->nullable);

        self::assertNotNull($this->findSection($resultItemPayload->sections, 'restrictions'));
        self::assertNotNull($this->findSection($resultItemPayload->sections, 'entity_link'));
        self::assertNotNull($this->findSection($resultItemPayload->sections, 'permissions'));
        self::assertNotNull($this->findSection($resultItemPayload->sections, 'readed_list_item'));
        self::assertNotNull($this->findSection($resultItemPayload->sections, 'last_message_views'));

        $resultItemPayloadSection = $this->findSection($resultItemPayload->sections, 'readed_list_item');
        self::assertNotNull($resultItemPayloadSection);

        $date = $this->findField($resultItemPayloadSection->fields, 'date');
        self::assertNotNull($date);
        self::assertSame(CarbonImmutable::class . '|null', $date->phpdocType);
        self::assertSame('date-time', $date->format);
        self::assertFalse($date->required);
        self::assertTrue($date->nullable);
    }

    #[Test]
    public function itRejectsHtmlInputBecauseOnlyMarkdownRepoContentIsSupported(): void
    {
        $restDocsResultItemPayloadProvider = new RestDocsResultItemPayloadProvider();

        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage('Object "result-item" was not found');

        $restDocsResultItemPayloadProvider->provide(
            markdownFile: __DIR__ . '/../Fixtures/im-dialog-get.html',
            method: 'im.dialog.get',
        );
    }

    /**
     * @param list<\Bitrix24\SDK\OpenApi\Domain\ResultItem\Payload\ResultItemPayloadField> $fields
     */
    private function findField(array $fields, string $code): ?\Bitrix24\SDK\OpenApi\Domain\ResultItem\Payload\ResultItemPayloadField
    {
        foreach ($fields as $field) {
            if ($field->code === $code) {
                return $field;
            }
        }

        return null;
    }

    /**
     * @param list<\Bitrix24\SDK\OpenApi\Domain\ResultItem\Payload\ResultItemPayloadSection> $sections
     */
    private function findSection(array $sections, string $name): ?\Bitrix24\SDK\OpenApi\Domain\ResultItem\Payload\ResultItemPayloadSection
    {
        foreach ($sections as $section) {
            if ($section->name === $name) {
                return $section;
            }
        }

        return null;
    }
}
