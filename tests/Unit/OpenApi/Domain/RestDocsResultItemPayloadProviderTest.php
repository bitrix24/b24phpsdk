<?php

declare(strict_types=1);

namespace Bitrix24\SDK\Tests\Unit\OpenApi\Domain;

use Bitrix24\SDK\OpenApi\Domain\ResultItem\RestDocsResultItemPayloadProvider;
use Carbon\CarbonImmutable;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use RuntimeException;

final class RestDocsResultItemPayloadProviderTest extends TestCase
{
    private const string DOCS_FIXTURE = __DIR__ . '/Fixtures/im-dialog-get.md';

    #[Test]
    public function itBuildsResultItemPayloadFromMarkdownDocumentation(): void
    {
        $provider = new RestDocsResultItemPayloadProvider();

        $payload = $provider->provide(
            markdownFile: self::DOCS_FIXTURE,
            method: 'im.dialog.get',
        );

        self::assertSame('im.dialog.get', $payload->method);
        self::assertSame('result-item', $payload->object);
        self::assertSame(['b24restdocs'], $payload->generatedFrom);

        $dateCreate = $this->findField($payload->fields, 'date_create');
        self::assertNotNull($dateCreate);
        self::assertSame('datetime', $dateCreate->sourceType);
        self::assertSame(CarbonImmutable::class, $dateCreate->phpdocType);
        self::assertSame('date-time', $dateCreate->format);
        self::assertTrue($dateCreate->required);
        self::assertFalse($dateCreate->nullable);

        self::assertNotNull($this->findSection($payload->sections, 'restrictions'));
        self::assertNotNull($this->findSection($payload->sections, 'entity_link'));
        self::assertNotNull($this->findSection($payload->sections, 'permissions'));
        self::assertNotNull($this->findSection($payload->sections, 'readed_list_item'));
        self::assertNotNull($this->findSection($payload->sections, 'last_message_views'));

        $readedListItem = $this->findSection($payload->sections, 'readed_list_item');
        self::assertNotNull($readedListItem);

        $date = $this->findField($readedListItem->fields, 'date');
        self::assertNotNull($date);
        self::assertSame(CarbonImmutable::class . '|null', $date->phpdocType);
        self::assertSame('date-time', $date->format);
        self::assertFalse($date->required);
        self::assertTrue($date->nullable);
    }

    #[Test]
    public function itRejectsHtmlInputBecauseOnlyMarkdownRepoContentIsSupported(): void
    {
        $provider = new RestDocsResultItemPayloadProvider();

        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage('Object "result-item" was not found');

        $provider->provide(
            markdownFile: __DIR__ . '/Fixtures/im-dialog-get.html',
            method: 'im.dialog.get',
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

    /**
     * @param list<\Bitrix24\SDK\OpenApi\Domain\ResultItem\ResultItemPayloadSection> $sections
     */
    private function findSection(array $sections, string $name): ?\Bitrix24\SDK\OpenApi\Domain\ResultItem\ResultItemPayloadSection
    {
        foreach ($sections as $section) {
            if ($section->name === $name) {
                return $section;
            }
        }

        return null;
    }
}
