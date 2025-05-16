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

namespace Bitrix24\SDK\Tests\Integration\Services\CRM\Lead\Service;

use Bitrix24\SDK\Core\Exceptions\BaseException;
use Bitrix24\SDK\Core\Exceptions\TransportException;
use Bitrix24\SDK\Core;
use Bitrix24\SDK\Services\CRM\Contact\Result\ContactItemResult;
use Bitrix24\SDK\Services\CRM\Lead\Result\LeadItemResult;
use Bitrix24\SDK\Services\CRM\Lead\Service\Lead;
use Bitrix24\SDK\Tests\CustomAssertions\CustomBitrix24Assertions;
use Bitrix24\SDK\Tests\Integration\Fabric;
use PHPUnit\Framework\Attributes\CoversFunction;
use PHPUnit\Framework\Attributes\CoversMethod;
use PHPUnit\Framework\TestCase;

/**
 * Class LeadTest
 *
 * @package Bitrix24\SDK\Tests\Integration\Services\CRM\Lead\Service
 */
#[CoversMethod(Lead::class,'add')]
#[CoversMethod(Lead::class,'delete')]
#[CoversMethod(Lead::class,'get')]
#[CoversMethod(Lead::class,'list')]
#[CoversMethod(Lead::class,'fields')]
#[CoversMethod(Lead::class,'update')]
#[\PHPUnit\Framework\Attributes\CoversClass(\Bitrix24\SDK\Services\CRM\Deal\Service\Deal::class)]
class LeadTest extends TestCase
{
    use CustomBitrix24Assertions;
    protected Lead $leadService;

    public function testAllSystemFieldsAnnotated(): void
    {
        $propListFromApi = (new Core\Fields\FieldsFilter())->filterSystemFields(array_keys($this->leadService->fields()->getFieldsDescription()));
        $this->assertBitrix24AllResultItemFieldsAnnotated($propListFromApi, LeadItemResult::class);
    }

    public function testAllSystemFieldsHasValidTypeAnnotation():void
    {
        $allFields = $this->leadService->fields()->getFieldsDescription();
        $systemFieldsCodes = (new Core\Fields\FieldsFilter())->filterSystemFields(array_keys($allFields));
        $systemFields = array_filter($allFields, static fn($code): bool => in_array($code, $systemFieldsCodes, true), ARRAY_FILTER_USE_KEY);

        $this->assertBitrix24AllResultItemFieldsHasValidTypeAnnotation(
            $systemFields,
            LeadItemResult::class);
    }

    /**
     * @throws BaseException
     * @throws TransportException
     */
    public function testAdd(): void
    {
        self::assertGreaterThan(1, $this->leadService->add(['TITLE' => 'test lead'])->getId());
    }

    /**
     * @throws BaseException
     * @throws TransportException
     */
    public function testDelete(): void
    {
        self::assertTrue($this->leadService->delete($this->leadService->add(['TITLE' => 'test lead'])->getId())->isSuccess());
    }

    /**
     * @throws BaseException
     * @throws TransportException
     */
    public function testFields(): void
    {
        self::assertIsArray($this->leadService->fields()->getFieldsDescription());
    }

    /**
     * @throws BaseException
     * @throws TransportException
     */
    public function testGet(): void
    {
        self::assertGreaterThan(
            1,
            $this->leadService->get($this->leadService->add(['TITLE' => 'test Lead'])->getId())->lead()->ID
        );
    }

    /**
     * @throws BaseException
     * @throws TransportException
     */
    public function testList(): void
    {
        $this->leadService->add(['TITLE' => 'test']);
        self::assertGreaterThanOrEqual(1, $this->leadService->list([], [], ['ID', 'TITLE'])->getLeads());
    }

    /**
     * @throws BaseException
     * @throws TransportException
     */
    public function testUpdate(): void
    {
        $addedItemResult = $this->leadService->add(['TITLE' => 'test lead']);
        $newTitle = 'test2';

        self::assertTrue($this->leadService->update($addedItemResult->getId(), ['TITLE' => $newTitle], [])->isSuccess());
        self::assertEquals($newTitle, $this->leadService->get($addedItemResult->getId())->lead()->TITLE);
    }

    /**
     * @throws \Bitrix24\SDK\Core\Exceptions\BaseException
     * @throws \Bitrix24\SDK\Core\Exceptions\TransportException
     */
    public function testCountByFilter(): void
    {
        $before = $this->leadService->countByFilter();

        $newItemsCount = 60;
        $items = [];
        for ($i = 1; $i <= $newItemsCount; $i++) {
            $items[] = ['TITLE' => 'TITLE-' . $i];
        }

        $cnt = 0;
        foreach ($this->leadService->batch->add($items) as $item) {
            $cnt++;
        }

        self::assertEquals(count($items), $cnt);

        $after = $this->leadService->countByFilter();

        $this->assertEquals($before + $newItemsCount, $after);
    }

    protected function setUp(): void
    {
        $this->leadService = Fabric::getServiceBuilder()->getCRMScope()->lead();
    }
}