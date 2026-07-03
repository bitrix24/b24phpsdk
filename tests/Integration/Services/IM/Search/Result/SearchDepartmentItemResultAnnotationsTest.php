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
use Bitrix24\SDK\Services\IM\Search\Result\SearchDepartmentItemResult;
use Bitrix24\SDK\Services\IM\Search\Service\Search;
use Bitrix24\SDK\Tests\CustomAssertions\CustomBitrix24Assertions;
use Bitrix24\SDK\Tests\Integration\Fabric;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\Attributes\TestDox;
use PHPUnit\Framework\TestCase;

#[CoversClass(SearchDepartmentItemResult::class)]
class SearchDepartmentItemResultAnnotationsTest extends TestCase
{
    use CustomBitrix24Assertions;

    private Search $searchService;

    #[\Override]
    protected function setUp(): void
    {
        $this->searchService = Fabric::getServiceBuilder()->getIMScope()->search();
    }

    /**
     * @throws BaseException
     * @throws TransportException
     */
    #[Test]
    #[TestDox('all fields in SearchDepartmentItemResult are annotated in phpdoc and match with raw api response')]
    public function testAllSystemFieldsAnnotated(): void
    {
        $rawResult = $this->searchService->departmentList(find: 'Отд', userData: true, limit: 1)
            ->getCoreResponse()
            ->getResponseData()
            ->getResult();

        if ($rawResult === []) {
            $this->markTestSkipped('No department search results available to validate annotations');
        }

        $firstItem = reset($rawResult);
        if (!is_array($firstItem)) {
            $this->markTestSkipped('Unexpected response shape for im.search.department.list');
        }

        $this->assertBitrix24AllResultItemFieldsAnnotated(
            array_keys($firstItem),
            SearchDepartmentItemResult::class
        );
    }

    /**
     * @throws BaseException
     * @throws TransportException
     */
    #[Test]
    #[TestDox('all fields in SearchDepartmentItemResult have valid type casting in magic getters')]
    public function testAllSystemFieldsHasValidTypeAnnotation(): void
    {
        $items = $this->searchService->departmentList(find: 'Отд', userData: true, limit: 1)->items();

        if ($items === []) {
            $this->markTestSkipped('No department search results available to validate type casting');
        }

        $this->assertBitrix24ResultItemFieldsTypeCastMatchAnnotations(
            $items[0],
            SearchDepartmentItemResult::class
        );
    }
}
