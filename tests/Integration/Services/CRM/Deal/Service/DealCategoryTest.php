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
use Bitrix24\SDK\Services\CRM\Deal\Service\DealCategory;
use Bitrix24\SDK\Tests\Integration\Fabric;
use PHPUnit\Framework\TestCase;

/**
 * Class DealCategoryTest
 *
 * @package Bitrix24\SDK\Tests\Integration\Services\CRM\Deals\Service
 */
#[\PHPUnit\Framework\Attributes\CoversClass(\Bitrix24\SDK\Services\CRM\Deal\Service\DealCategory::class)]
class DealCategoryTest extends TestCase
{
    protected DealCategory $dealCategory;

    /**
     *
     * @throws BaseException
     * @throws TransportException
     */
    public function testAdd(): void
    {
        $countBefore = $this->dealCategory->list([], [], [], 0)->getCoreResponse()->getResponseData()->getPagination()->getTotal();
        $this::assertGreaterThanOrEqual(
            1,
            $this->dealCategory->add(
                [
                    'NAME' => 'test',
                    'SORT' => 20,
                ]
            )->getId()
        );
        $countAfter = $this->dealCategory->list([], [], [], 0)->getCoreResponse()->getResponseData()->getPagination()->getTotal();

        $this::assertEquals($countBefore + 1, $countAfter);
    }

    /**
     * @throws BaseException
     * @throws TransportException
     */
    public function testDelete(): void
    {
        $this::assertTrue(
            $this->dealCategory->delete(
                $this->dealCategory->add(
                    [
                        'NAME' => 'test_name',
                    ]
                )->getId()
            )->isSuccess()
        );
    }

    /**
     * @throws BaseException
     * @throws TransportException
     */
    public function testFields(): void
    {
        $this::assertIsArray($this->dealCategory->fields()->getFieldsDescription());
    }

    /**
     * @throws BaseException
     * @throws TransportException
     */
    public function testDealCategoryDefaultGet(): void
    {
        $this::assertGreaterThanOrEqual(0, $this->dealCategory->getDefaultCategorySettings()->getDealCategoryFields()->ID);
    }

    /**
     * @throws BaseException
     * @throws TransportException
     */
    public function testDealCategoryDefaultSet(): void
    {
        $oldName = $this->dealCategory->getDefaultCategorySettings()->getDealCategoryFields()->NAME;
        $newName = (string)time();
        $this::assertTrue($this->dealCategory->setDefaultCategorySettings(['NAME' => $newName])->isSuccess());
        $this::assertNotSame($oldName, $this->dealCategory->getDefaultCategorySettings()->getDealCategoryFields()->NAME);
    }

    /**
     * @throws BaseException
     * @throws TransportException
     */
    public function testDealCategoryGet(): void
    {
        $newCategory = [
            'NAME' => 'test new deal category',
            'SORT' => 300,
        ];

        $newCategoryId = $this->dealCategory->add($newCategory)->getId();
        $dealCategoryResult = $this->dealCategory->get($newCategoryId);

        $this::assertEquals($newCategory['NAME'], $dealCategoryResult->getDealCategoryFields()->NAME);
        $this::assertEquals($newCategory['SORT'], $dealCategoryResult->getDealCategoryFields()->SORT);
    }

    /**
     *
     * @throws BaseException
     * @throws TransportException
     */
    public function testList(): void
    {
        $dealCategoriesResult = $this->dealCategory->list([], [], [], 0);
        $this::assertGreaterThanOrEqual(1, count($dealCategoriesResult->getDealCategories()));
    }

    /**
     * @throws BaseException
     * @throws TransportException
     */
    public function testDealCategoryStatus(): void
    {
        $newCategory = [
            'NAME' => 'test new deal category',
            'SORT' => 300,
        ];
        $newCategoryId = $this->dealCategory->add($newCategory)->getId();
        $dealCategoryStatusResult = $this->dealCategory->getStatus($newCategoryId);
        $this::assertGreaterThan(1, strlen($dealCategoryStatusResult->getDealCategoryTypeId()));
    }

    /**
     * @throws BaseException
     * @throws TransportException
     */
    public function testUpdate(): void
    {
        $newCategory = [
            'NAME' => 'test new deal category',
            'SORT' => 300,
        ];
        $newCategoryId = $this->dealCategory->add($newCategory)->getId();
        $this::assertTrue($this->dealCategory->update($newCategoryId, ['NAME' => 'updated'])->isSuccess());
        $this::assertEquals('updated', $this->dealCategory->get($newCategoryId)->getDealCategoryFields()->NAME);
    }

    protected function setUp(): void
    {
        $this->dealCategory = Fabric::getServiceBuilder()->getCRMScope()->dealCategory();
    }
}