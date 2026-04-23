<?php

declare(strict_types=1);

namespace Bitrix24\SDK\Tests\Unit\OpenApi\Domain;

use Bitrix24\SDK\OpenApi\Domain\ResultItem\OfficialDocumentationResultFieldProvider;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use Symfony\Component\HttpClient\MockHttpClient;
use Symfony\Component\HttpClient\Response\MockResponse;

class OfficialDocumentationResultFieldProviderTest extends TestCase
{
    private const string HTML_FIXTURE = __DIR__ . '/Fixtures/im-dialog-get.html';
    private const string DIPLODOC_STATE_FIXTURE = __DIR__ . '/Fixtures/im-dialog-get-diplodoc-state.html';

    #[Test]
    public function itParsesFieldsFromReturnedDataObjectTable(): void
    {
        $provider = new OfficialDocumentationResultFieldProvider(
            new MockHttpClient(new MockResponse((string) file_get_contents(self::HTML_FIXTURE)))
        );

        $fields = $provider->provide('https://apidocs.bitrix24.com/api-reference/chats/im-dialog-get.html', 'result-item');

        $this->assertNotNull($fields);
        $this->assertCount(5, $fields);
        $this->assertSame('id', $fields[0]->name);
        $this->assertSame('integer', $fields[0]->type);
        $this->assertSame('date_create', $fields[1]->name);
        $this->assertSame('string', $fields[1]->type);
        $this->assertSame('date-time', $fields[1]->format);
        $this->assertTrue($fields[3]->nullable);
        $this->assertSame('object', $fields[4]->type);
    }

    #[Test]
    public function itParsesFieldsFromDiplodocStateEmbeddedHtml(): void
    {
        $provider = new OfficialDocumentationResultFieldProvider(
            new MockHttpClient(new MockResponse((string) file_get_contents(self::DIPLODOC_STATE_FIXTURE)))
        );

        $fields = $provider->provide('https://apidocs.bitrix24.com/api-reference/chats/im-dialog-get.html', 'result-item');

        $this->assertNotNull($fields);
        $this->assertCount(2, $fields);
        $this->assertSame('id', $fields[0]->name);
        $this->assertSame('date_create', $fields[1]->name);
        $this->assertSame('date-time', $fields[1]->format);
    }
}
