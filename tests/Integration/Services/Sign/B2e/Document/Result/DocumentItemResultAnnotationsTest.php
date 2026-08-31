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

namespace Bitrix24\SDK\Tests\Integration\Services\Sign\B2e\Document\Result;

use Bitrix24\SDK\Core\Exceptions\BaseException;
use Bitrix24\SDK\Core\Exceptions\TransportException;
use Bitrix24\SDK\Services\Sign\B2e\Document\Result\DocumentItemResult;
use Bitrix24\SDK\Services\Sign\B2e\Document\Service\Document;
use Bitrix24\SDK\Tests\CustomAssertions\CustomBitrix24Assertions;
use Bitrix24\SDK\Tests\Integration\Fabric;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\Attributes\TestDox;
use PHPUnit\Framework\TestCase;

#[CoversClass(DocumentItemResult::class)]
class DocumentItemResultAnnotationsTest extends TestCase
{
    use CustomBitrix24Assertions;

    private Document $documentService;

    private ?string $documentUid = null;

    #[\Override]
    protected function setUp(): void
    {
        $this->documentService = Fabric::getServiceBuilder(true)->getSignScope()->document();

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
    #[TestDox('testAllSystemFieldsAnnotated: all fields in DocumentItemResult are annotated in phpdoc and match raw API response')]
    public function testAllSystemFieldsAnnotated(): void
    {
        if ($this->documentUid === null) {
            $this->markTestSkipped(
                'Set SIGN_B2E_DOCUMENT_UID in tests/.env.local to enable this test.'
            );
        }

        $rawResult = $this->documentService->get($this->documentUid)
            ->getCoreResponse()->getResponseData()->getResult();

        $this->assertBitrix24AllResultItemFieldsAnnotated(
            array_keys($rawResult),
            DocumentItemResult::class
        );
    }

    /**
     * @throws BaseException
     * @throws TransportException
     */
    #[Test]
    #[TestDox('testAllSystemFieldsHasValidTypeAnnotation: all fields in DocumentItemResult have valid type annotations')]
    public function testAllSystemFieldsHasValidTypeAnnotation(): void
    {
        if ($this->documentUid === null) {
            $this->markTestSkipped(
                'Set SIGN_B2E_DOCUMENT_UID in tests/.env.local to enable this test.'
            );
        }

        $rawResult = $this->documentService->get($this->documentUid)
            ->getCoreResponse()->getResponseData()->getResult();

        $fieldTypesMap = [];
        foreach (array_keys($rawResult) as $fieldCode) {
            $fieldTypesMap[$fieldCode] = match ($fieldCode) {
                'uid' => ['type' => 'string'],
                'state', 'members' => ['type' => 'array'],
                default => throw new \RuntimeException(
                    sprintf('Unknown field «%s» in sign.b2e.document.get response — update the type map.', $fieldCode)
                ),
            };
        }

        $this->assertBitrix24AllResultItemFieldsHasValidTypeAnnotation(
            $fieldTypesMap,
            DocumentItemResult::class
        );
    }
}
