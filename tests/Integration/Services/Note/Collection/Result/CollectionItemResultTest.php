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

namespace Bitrix24\SDK\Tests\Integration\Services\Note\Collection\Result;

use Bitrix24\SDK\Core\Exceptions\BaseException;
use Bitrix24\SDK\Core\Exceptions\TransportException;
use Bitrix24\SDK\Services\Note\Collection\Result\CollectionItemResult;
use Bitrix24\SDK\Services\Note\Collection\Service\Collection;
use Bitrix24\SDK\Tests\CustomAssertions\CustomBitrix24Assertions;
use Bitrix24\SDK\Tests\Integration\Factory;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\Attributes\TestDox;
use PHPUnit\Framework\TestCase;

#[CoversClass(CollectionItemResult::class)]
class CollectionItemResultTest extends TestCase
{
    use CustomBitrix24Assertions;

    private Collection $collectionService;

    private int $collectionId;

    /**
     * @throws BaseException
     * @throws TransportException
     */
    #[\Override]
    protected function setUp(): void
    {
        $this->collectionService = Factory::getServiceBuilder()->getNoteScope()->collection();
        $this->collectionId = $this->collectionService->add('SDK annotation test collection ' . time())
            ->collection()->id;
    }

    /**
     * @throws BaseException
     * @throws TransportException
     */
    #[\Override]
    protected function tearDown(): void
    {
        $this->collectionService->delete($this->collectionId);
    }

    /**
     * @throws BaseException
     * @throws TransportException
     */
    #[Test]
    #[TestDox('all fields in CollectionItemResult are annotated in phpdoc and match with raw api response')]
    public function testAllFieldsAreAnnotated(): void
    {
        $rawItem = $this->collectionService->get($this->collectionId)
            ->getCoreResponse()->getResponseData()->getResult()['item'];

        $this->assertBitrix24AllResultItemFieldsAnnotated(array_keys($rawItem), CollectionItemResult::class);
    }

    /**
     * @throws BaseException
     * @throws TransportException
     */
    #[Test]
    #[TestDox('all fields in CollectionItemResult have valid type casting in magic getters')]
    public function testAllFieldsHasValidTypeCastingInMagicGetters(): void
    {
        $collection = $this->collectionService->get($this->collectionId)->collection();
        $this->assertBitrix24ResultItemFieldsTypeCastMatchAnnotations($collection, CollectionItemResult::class);
    }
}
