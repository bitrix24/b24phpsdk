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

namespace Bitrix24\SDK\Tests\Integration\Services\Catalog\UserfieldDocument\Service;

use Bitrix24\SDK\Core\Exceptions\BaseException;
use Bitrix24\SDK\Core\Exceptions\TransportException;
use Bitrix24\SDK\Services\Catalog\UserfieldDocument\Result\UserfieldDocumentsResult;
use Bitrix24\SDK\Services\Catalog\UserfieldDocument\Service\UserfieldDocument;
use Bitrix24\SDK\Tests\Integration\Fabric;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\TestDox;
use PHPUnit\Framework\TestCase;

#[CoversClass(UserfieldDocument::class)]
class UserfieldDocumentTest extends TestCase
{
    private UserfieldDocument $userfieldDocumentService;

    #[\Override]
    protected function setUp(): void
    {
        $this->userfieldDocumentService = Fabric::getServiceBuilder()->getCatalogScope()->userfieldDocument();
    }

    /**
     * @throws BaseException
     * @throws TransportException
     */
    #[TestDox('test UserfieldDocument::list')]
    public function testList(): void
    {
        $userfieldDocumentsResult = $this->userfieldDocumentService->list(
            ['documentType', 'documentId'],
            ['documentType' => 'A', 'documentId' => 0]
        );

        $this->assertInstanceOf(UserfieldDocumentsResult::class, $userfieldDocumentsResult);
        $this->assertCount(0, $userfieldDocumentsResult->getDocuments());
    }

    /**
     * A non-existent documentId is rejected by the API before any userfield values are touched,
     * so this is exercised without needing to create a real warehouse accounting document.
     */
    #[TestDox('test UserfieldDocument::update with non-existent document')]
    public function testUpdateWithNonExistentDocumentThrowsException(): void
    {
        $this->expectException(BaseException::class);
        $this->userfieldDocumentService->update(0, ['documentType' => 'A']);
    }
}
