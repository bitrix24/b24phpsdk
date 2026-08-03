<?php

/**
 * This file is part of the bitrix24-php-sdk package.
 *
 * © Dmitriy Ignatenko <algonexys@gmail.com>
 *
 * For the full copyright and license information, please view the MIT-LICENSE.txt
 * file that was distributed with this source code.
 */

declare(strict_types=1);

namespace Bitrix24\SDK\Tests\Integration\Services\Sign\B2e\Document\Service;

use Bitrix24\SDK\Core\Exceptions\BaseException;
use Bitrix24\SDK\Core\Exceptions\TransportException;
use Bitrix24\SDK\Services\Sign\B2e\Document\Result\DocumentItemResult;
use Bitrix24\SDK\Services\Sign\B2e\Document\Result\DocumentResult;
use Bitrix24\SDK\Services\Sign\B2e\Document\Service\Document;
use Bitrix24\SDK\Tests\Integration\Factory;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\Attributes\TestDox;
use PHPUnit\Framework\TestCase;

#[CoversClass(Document::class)]
class DocumentTest extends TestCase
{
    private Document $documentService;

    private ?string $documentUid = null;

    #[\Override]
    protected function setUp(): void
    {
        $this->documentService = Factory::getServiceBuilder(true)->getSignScope()->document();

        $value = $_ENV['SIGN_B2E_DOCUMENT_UID'] ?? '';
        if ($value !== '') {
            $this->documentUid = $value;
        }
    }

    /**
     * @throws BaseException
     * @throws TransportException
     */
    #[Test]
    #[TestDox('sign.b2e.document.get returns DocumentResult for a known document UID')]
    public function testGetReturnsDocumentResult(): void
    {
        if ($this->documentUid === null) {
            $this->markTestSkipped(
                'Set SIGN_B2E_DOCUMENT_UID in tests/.env.local to enable this test.'
            );
        }

        $documentResult = $this->documentService->get($this->documentUid);

        self::assertInstanceOf(DocumentResult::class, $documentResult);

        $documentItemResult = $documentResult->getDocument();
        self::assertInstanceOf(DocumentItemResult::class, $documentItemResult);
        self::assertIsString($documentItemResult->uid);
        self::assertEquals($this->documentUid, $documentItemResult->uid);
        self::assertIsArray($documentItemResult->state);
        self::assertArrayHasKey('code', $documentItemResult->state);
        self::assertArrayHasKey('name', $documentItemResult->state);
    }

    /**
     * @throws TransportException
     */
    #[Test]
    #[TestDox('sign.b2e.document.get throws BaseException for non-existent document UID')]
    public function testGetThrowsExceptionForInvalidUid(): void
    {
        $this->expectException(BaseException::class);

        $this->documentService->get('non-existent-uid-00000000-0000-0000-0000-000000000000');
    }

    /**
     * @throws TransportException
     */
    #[Test]
    #[TestDox('sign.b2e.document.send throws BaseException when required fields are missing')]
    public function testSendThrowsExceptionWhenRequiredFieldsMissing(): void
    {
        $this->expectException(BaseException::class);

        // Sending with empty fields should return BAD_REQUEST from the API
        $this->documentService->send([]);
    }
}
