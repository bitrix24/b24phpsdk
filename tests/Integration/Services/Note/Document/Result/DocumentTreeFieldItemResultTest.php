<?php

/**
 * This file is part of the bitrix24-php-sdk package.
 *
 * © Maksim Mesilov <mesilov.maxim@gmail.com>
 *
 * For the full copyright and license information, please view the MIT-LICENSE.txt
 * file that was distributed with this source code.
 */

declare(strict_types=1);

namespace Bitrix24\SDK\Tests\Integration\Services\Note\Document\Result;

use Bitrix24\SDK\Core\Exceptions\BaseException;
use Bitrix24\SDK\Core\Exceptions\TransportException;
use Bitrix24\SDK\Services\Note\Document\Result\DocumentTreeFieldItemResult;
use Bitrix24\SDK\Services\Note\Document\Service\Document;
use Bitrix24\SDK\Tests\CustomAssertions\CustomBitrix24Assertions;
use Bitrix24\SDK\Tests\Integration\Factory;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\Attributes\TestDox;
use PHPUnit\Framework\TestCase;

#[CoversClass(DocumentTreeFieldItemResult::class)]
class DocumentTreeFieldItemResultTest extends TestCase
{
    use CustomBitrix24Assertions;

    private Document $documentService;

    #[\Override]
    protected function setUp(): void
    {
        $this->documentService = Factory::getServiceBuilder()->getNoteScope()->document();
    }

    /**
     * @throws BaseException
     * @throws TransportException
     */
    #[Test]
    #[TestDox('all fields in DocumentTreeFieldItemResult are annotated in phpdoc and match with raw api response')]
    public function testAllFieldsAreAnnotated(): void
    {
        $rawItem = $this->documentService->treeFieldGet('title')
            ->getCoreResponse()->getResponseData()->getResult()['item'];

        $this->assertBitrix24AllResultItemFieldsAnnotated(array_keys($rawItem), DocumentTreeFieldItemResult::class);
    }

    /**
     * @throws BaseException
     * @throws TransportException
     */
    #[Test]
    #[TestDox('all fields in DocumentTreeFieldItemResult have valid type casting in magic getters')]
    public function testAllFieldsHasValidTypeCastingInMagicGetters(): void
    {
        $field = $this->documentService->treeFieldGet('title')->field();
        $this->assertBitrix24ResultItemFieldsTypeCastMatchAnnotations($field, DocumentTreeFieldItemResult::class);
    }
}
