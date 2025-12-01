<?php

/**
 * This file is part of the bitrix24-php-sdk package.
 *
 * © Vadim Soluyanov <vadimsallee@gmail.com>
 *
 * For the full copyright and license information, please view the MIT-LICENSE.txt
 * file that was distributed with this source code.
 */

declare(strict_types=1);

namespace Bitrix24\SDK\Tests\Integration\Services\CRM\Quote\Service;

use Bitrix24\SDK\Core\Exceptions\BaseException;
use Bitrix24\SDK\Core\Exceptions\TransportException;
use Bitrix24\SDK\Core;
use Bitrix24\SDK\Services\CRM\Quote\Result\QuoteItemResult;
use Bitrix24\SDK\Services\CRM\Quote\Service\Quote;
use Bitrix24\SDK\Tests\CustomAssertions\CustomBitrix24Assertions;
use Bitrix24\SDK\Tests\Integration\Factory;
use PHPUnit\Framework\Attributes\CoversFunction;
use PHPUnit\Framework\Attributes\CoversMethod;
use PHPUnit\Framework\TestCase;

/**
 * Class QuoteTest
 *
 * @package Bitrix24\SDK\Tests\Integration\Services\CRM\Quote\Service
 */
#[CoversMethod(Quote::class,'add')]
#[CoversMethod(Quote::class,'delete')]
#[CoversMethod(Quote::class,'get')]
#[CoversMethod(Quote::class,'list')]
#[CoversMethod(Quote::class,'fields')]
#[CoversMethod(Quote::class,'update')]
#[\PHPUnit\Framework\Attributes\CoversClass(\Bitrix24\SDK\Services\CRM\Quote\Service\Quote::class)]
class QuoteTest extends TestCase
{
    use CustomBitrix24Assertions;
    protected Quote $quoteService;
    
    protected function setUp(): void
    {
        $this->quoteService = Factory::getServiceBuilder()->getCRMScope()->quote();
    }

    public function testAllSystemFieldsAnnotated(): void
    {
        $propListFromApi = (new Core\Fields\FieldsFilter())->filterSystemFields(array_keys($this->quoteService->fields()->getFieldsDescription()));
        $this->assertBitrix24AllResultItemFieldsAnnotated($propListFromApi, QuoteItemResult::class);
    }

    public function testAllSystemFieldsHasValidTypeAnnotation():void
    {
        $allFields = $this->quoteService->fields()->getFieldsDescription();
        $systemFieldsCodes = (new Core\Fields\FieldsFilter())->filterSystemFields(array_keys($allFields));
        $systemFields = array_filter($allFields, static fn($code): bool => in_array($code, $systemFieldsCodes, true), ARRAY_FILTER_USE_KEY);

        $this->assertBitrix24AllResultItemFieldsHasValidTypeAnnotation(
            $systemFields,
            QuoteItemResult::class);
    }

    /**
     * @throws BaseException
     * @throws TransportException
     */
    public function testAdd(): void
    {
        self::assertGreaterThan(1, $this->quoteService->add(['TITLE' => 'test quote'])->getId());
    }

    /**
     * @throws BaseException
     * @throws TransportException
     */
    public function testDelete(): void
    {
        self::assertTrue($this->quoteService->delete($this->quoteService->add(['TITLE' => 'test quote 1'])->getId())->isSuccess());
    }

    /**
     * @throws BaseException
     * @throws TransportException
     */
    public function testFields(): void
    {
        self::assertIsArray($this->quoteService->fields()->getFieldsDescription());
    }

    /**
     * @throws BaseException
     * @throws TransportException
     */
    public function testGet(): void
    {
        self::assertGreaterThan(
            1,
            $this->quoteService->get($this->quoteService->add(['TITLE' => 'test Quote 2'])->getId())->quote()->ID
        );
    }

    /**
     * @throws BaseException
     * @throws TransportException
     */
    public function testList(): void
    {
        $this->quoteService->add(['TITLE' => 'test']);
        self::assertGreaterThanOrEqual(1, $this->quoteService->list([], [], ['ID', 'TITLE'])->getQuotes());
    }

    /**
     * @throws BaseException
     * @throws TransportException
     */
    public function testUpdate(): void
    {
        $addedItemResult = $this->quoteService->add(['TITLE' => 'test quote']);
        $newTitle = 'test2';

        self::assertTrue($this->quoteService->update($addedItemResult->getId(), ['TITLE' => $newTitle])->isSuccess());
        self::assertEquals($newTitle, $this->quoteService->get($addedItemResult->getId())->quote()->TITLE);
    }

    /**
     * @throws \Bitrix24\SDK\Core\Exceptions\BaseException
     * @throws \Bitrix24\SDK\Core\Exceptions\TransportException
     */
    public function testCountByFilter(): void
    {
        $before = $this->quoteService->countByFilter();

        $newItemsCount = 20;
        $items = [];
        for ($i = 1; $i <= $newItemsCount; $i++) {
            $items[] = ['TITLE' => 'TITLE-' . $i];
        }

        $cnt = 0;
        foreach ($this->quoteService->batch->add($items) as $item) {
            $cnt++;
        }

        self::assertEquals(count($items), $cnt);

        $after = $this->quoteService->countByFilter();

        $this->assertEquals($before + $newItemsCount, $after);
    }
}
