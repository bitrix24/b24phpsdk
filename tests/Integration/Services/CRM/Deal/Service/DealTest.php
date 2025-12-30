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

namespace Bitrix24\SDK\Tests\Integration\Services\CRM\Deal\Service;

use Bitrix24\SDK\Core\Exceptions\BaseException;
use Bitrix24\SDK\Core\Exceptions\TransportException;
use Bitrix24\SDK\Services\CRM\Deal\Service\Deal;
use Bitrix24\SDK\Tests\Integration\Factory;
use PHPUnit\Framework\TestCase;
use Bitrix24\SDK\Core;
use Bitrix24\SDK\Tests\CustomAssertions\CustomBitrix24Assertions;
use Bitrix24\SDK\Services\CRM\Deal\Result\DealItemResult;

/**
 * Class DealsTest
 *
 * @package Bitrix24\SDK\Tests\Integration\Services\CRM\Deals\Service
 */
#[\PHPUnit\Framework\Attributes\CoversClass(\Bitrix24\SDK\Services\CRM\Deal\Service\Deal::class)]
class DealTest extends TestCase
{
    use CustomBitrix24Assertions;
    protected Deal $dealService;

    public function testAllSystemFieldsAnnotated(): void
    {
        $propListFromApi = (new Core\Fields\FieldsFilter())->filterSystemFields(array_keys($this->dealService->fields()->getFieldsDescription()));
        $this->assertBitrix24AllResultItemFieldsAnnotated($propListFromApi, DealItemResult::class);
    }

    /**
     * @throws BaseException
     * @throws TransportException
     */
    public function testAdd(): void
    {
        self::assertGreaterThan(1, $this->dealService->add(['TITLE' => 'test deal'])->getId());
    }

    /**
     * @throws BaseException
     * @throws TransportException
     */
    public function testDelete(): void
    {
        self::assertTrue($this->dealService->delete($this->dealService->add(['TITLE' => 'test deal'])->getId())->isSuccess());
    }

    /**
     * @throws BaseException
     * @throws TransportException
     */
    public function testFields(): void
    {
        self::assertIsArray($this->dealService->fields()->getFieldsDescription());
    }

    /**
     * @throws BaseException
     * @throws TransportException
     */
    public function testGet(): void
    {
        self::assertGreaterThan(
            1,
            $this->dealService->get($this->dealService->add(['TITLE' => 'test deal'])->getId())->deal()->ID
        );
    }

    /**
     * @throws BaseException
     * @throws TransportException
     */
    public function testList(): void
    {
        $this->dealService->add(['TITLE' => 'test']);
        self::assertGreaterThanOrEqual(1, $this->dealService->list([], [], ['ID', 'TITLE', 'TYPE_ID'])->getDeals());
    }

    public function testUpdate(): void
    {
        $addedItemResult = $this->dealService->add(['TITLE' => 'test']);
        $newTitle = 'test2';

        self::assertTrue($this->dealService->update($addedItemResult->getId(), ['TITLE' => $newTitle], [])->isSuccess());
        self::assertEquals($newTitle, $this->dealService->get($addedItemResult->getId())->deal()->TITLE);
    }

    /**
     * @throws \Bitrix24\SDK\Core\Exceptions\BaseException
     * @throws \Bitrix24\SDK\Core\Exceptions\TransportException
     */
    public function testCountByFilter(): void
    {
        $before = $this->dealService->countByFilter();

        $newDealsCount = 60;
        $deals = [];
        for ($i = 1; $i <= $newDealsCount; $i++) {
            $deals[] = ['TITLE' => 'TITLE-' . $i];
        }

        $cnt = 0;
        foreach ($this->dealService->batch->add($deals) as $item) {
            $cnt++;
        }

        self::assertEquals(count($deals), $cnt);

        $after = $this->dealService->countByFilter();

        $this->assertEquals($before + $newDealsCount, $after);
    }

    #[\Override]
    protected function setUp(): void
    {
        $this->dealService = Factory::getServiceBuilder()->getCRMScope()->deal();
    }
}