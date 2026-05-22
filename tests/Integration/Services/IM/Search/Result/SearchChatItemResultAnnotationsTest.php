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

namespace Bitrix24\SDK\Tests\Integration\Services\IM\Search\Result;

use Bitrix24\SDK\Core\Exceptions\BaseException;
use Bitrix24\SDK\Core\Exceptions\TransportException;
use Bitrix24\SDK\Services\IM\Search\Result\SearchChatItemResult;
use Bitrix24\SDK\Services\IM\Search\Service\Search;
use Bitrix24\SDK\Tests\CustomAssertions\CustomBitrix24Assertions;
use Bitrix24\SDK\Tests\Integration\Factory;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\Attributes\TestDox;
use PHPUnit\Framework\TestCase;

#[CoversClass(SearchChatItemResult::class)]
class SearchChatItemResultAnnotationsTest extends TestCase
{
    use CustomBitrix24Assertions;

    private Search $searchService;

    #[\Override]
    protected function setUp(): void
    {
        $this->searchService = Factory::getServiceBuilder()->getIMScope()->search();
    }

    /**
     * @throws BaseException
     * @throws TransportException
     */
    #[Test]
    #[TestDox('all fields in SearchChatItemResult are annotated in phpdoc and match with raw api response')]
    public function testAllSystemFieldsAnnotated(): void
    {
        $rawResult = $this->searchService->chatList(find: 'Test', limit: 1)
            ->getCoreResponse()
            ->getResponseData()
            ->getResult();

        if ($rawResult === []) {
            $this->markTestSkipped('No chat search results available to validate annotations');
        }

        $firstItem = reset($rawResult);
        if (!is_array($firstItem)) {
            $this->markTestSkipped('Unexpected response shape for im.search.chat.list');
        }

        $this->assertBitrix24AllResultItemFieldsAnnotated(
            array_keys($firstItem),
            SearchChatItemResult::class
        );
    }

    /**
     * @throws BaseException
     * @throws TransportException
     */
    #[Test]
    #[TestDox('all fields in SearchChatItemResult have valid type casting in magic getters')]
    public function testAllSystemFieldsHasValidTypeAnnotation(): void
    {
        $items = $this->searchService->chatList(find: 'Test', limit: 1)->items();

        if ($items === []) {
            $this->markTestSkipped('No chat search results available to validate type casting');
        }

        $this->assertBitrix24ResultItemFieldsTypeCastMatchAnnotations(
            $items[0],
            SearchChatItemResult::class
        );
    }
}
