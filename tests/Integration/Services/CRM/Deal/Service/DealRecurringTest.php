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

namespace Bitrix24\SDK\Tests\Integration\Services\CRM\Deal\Service;

use Bitrix24\SDK\Core\Exceptions\BaseException;
use Bitrix24\SDK\Core\Exceptions\TransportException;
use Bitrix24\SDK\Services\CRM\Deal\Service\DealRecurring;
use Bitrix24\SDK\Services\CRM\Deal\Service\Deal;
use Bitrix24\SDK\Tests\Integration\Fabric;
use PHPUnit\Framework\TestCase;

/**
 * Class DealRecurringTest
 *
 * @package Bitrix24\SDK\Tests\Integration\Services\CRM\Deals\Service
 */
class DealRecurringTest extends TestCase
{
    protected DealRecurring $dealRecurring;
    protected Deal $dealService;

    public function setUp(): void
    {
        $this->dealRecurring = Fabric::getServiceBuilder()->getCRMScope()->dealRecurring();
        $this->dealService = Fabric::getServiceBuilder()->getCRMScope()->deal();
        
    }
    
    /**
     * @covers \Bitrix24\SDK\Services\CRM\Deal\Service\DealRecurring::add
     *
     * @throws BaseException
     * @throws TransportException
     */
    public function testAdd(): void
    {
        $countBefore = $this->dealRecurring->list([], [], [], 0)->getCoreResponse()->getResponseData()->getPagination()->getTotal();
        $dealId = $this->dealService->add(['TITLE' => 'test recurring deal'])->getId();
        $recurringId = $this->dealRecurring->add($this->getRecurringFields($dealId))->getId();
        $this::assertGreaterThanOrEqual(
            1,
            $recurringId
        );
        $countAfter = $this->dealRecurring->list([], [], [], 0)->getCoreResponse()->getResponseData()->getPagination()->getTotal();

        $this::assertEquals($countBefore + 1, $countAfter);
        
        $this->dealService->delete($dealId);
        $this->dealRecurring->delete($recurringId);
    }

    /**
     * @covers \Bitrix24\SDK\Services\CRM\Deal\Service\DealRecurring::delete
     * @throws BaseException
     * @throws TransportException
     */
    public function testDelete(): void
    {
        $dealId = $this->dealService->add(['TITLE' => 'test recurring deal'])->getId();
        $recurringId = $this->dealRecurring->add($this->getRecurringFields($dealId))->getId();
        $this->dealService->delete($dealId);
        
        $this::assertTrue($this->dealRecurring->delete($recurringId)->isSuccess());
    }

    /**
     * @covers \Bitrix24\SDK\Services\CRM\Deal\Service\DealRecurring::fields
     * @throws BaseException
     * @throws TransportException
     */
    public function testFields(): void
    {
        $this::assertIsArray($this->dealRecurring->fields()->getFieldsDescription());
    }

    /**
     * @covers \Bitrix24\SDK\Services\CRM\Deal\Service\DealRecurring::get
     * @throws BaseException
     * @throws TransportException
     */
    public function testDealRecurringGet(): void
    {
        $dealId = $this->dealService->add(['TITLE' => 'test recurring deal'])->getId();
        $newRecurring = $this->getRecurringFields($dealId);
        $newRecurringId = $this->dealRecurring->add($newRecurring)->getId();
        $recurring = $this->dealRecurring->get($newRecurringId);

        $this::assertEquals($newRecurring['DEAL_ID'], $recurring->recurring()->DEAL_ID);
        
        $this->dealService->delete($dealId);
        $this->dealRecurring->delete($newRecurringId);
    }

    /**
     * @covers \Bitrix24\SDK\Services\CRM\Deal\Service\DealRecurring::list
     *
     * @throws BaseException
     * @throws TransportException
     */
    public function testList(): void
    {
        $dealId = $this->dealService->add(['TITLE' => 'test recurring deal'])->getId();
        $newRecurringId = $this->dealRecurring->add($this->getRecurringFields($dealId))->getId();
        $res = $this->dealRecurring->list([], [], [], 0);
        
        $this::assertGreaterThanOrEqual(1, count($res->getDealRecurrings()));
        
        $this->dealService->delete($dealId);
        $this->dealRecurring->delete($newRecurringId);
    }

    /**
     * @covers \Bitrix24\SDK\Services\CRM\Deal\Service\DealRecurring::expose
     * @throws BaseException
     * @throws TransportException
     */
    public function testExpose(): void
    {
        $dealId = $this->dealService->add(['TITLE' => 'test recurring deal'])->getId();
        $newRecurringId = $this->dealRecurring->add($this->getRecurringFields($dealId))->getId();
        $result = $this->dealRecurring->expose($newRecurringId);
        $this::assertGreaterThan(1, $result->getId());
        
        $this->dealService->delete($dealId);
        $this->dealRecurring->delete($newRecurringId);
    }

    /**
     * @covers \Bitrix24\SDK\Services\CRM\Deal\Service\DealRecurring::update
     * @throws BaseException
     * @throws TransportException
     */
    public function testUpdate(): void
    {
        $dealId = $this->dealService->add(['TITLE' => 'test recurring deal'])->getId();
        $newRecurring = $this->getRecurringFields($dealId);
        $newRecurringId = $this->dealRecurring->add($newRecurring)->getId();
        $dateStart = new \DateTime();
        $dateStart->modify('+2 month');
        $dateStartStr = $dateStart->format(\DateTime::ATOM);
        $this::assertTrue($this->dealRecurring->update($newRecurringId, ['START_DATE' => $dateStartStr])->isSuccess());
        $this::assertEquals($dateStartStr, $this->dealRecurring->get($newCategoryId)->recurring()->START_DATE);
        
        $this->dealService->delete($dealId);
        $this->dealRecurring->delete($newRecurringId);
    }
    
    protected function getRecurringFields(int $dealId) {
        $dateLimit = new \DateTime();
        $dateLimit->modify('+1 year');
        $dateLimitStr = $dateLimit->format(\DateTime::ATOM);
        $dateStart = new \DateTime();
        $dateStart->modify('+1 month');
        $dateStartStr = $dateStart->format(\DateTime::ATOM);
        
        return [
            'DEAL_ID' => $dealId,
            'CATEGORY_ID' => 0,
            'IS_LIMIT' => 'D',
            'LIMIT_DATE' => $dateLimitStr,
            'START_DATE' => $dateStartStr,
            'PARAMS' => [
                'MODE' => 'multiple',
                'MULTIPLE_TYPE' => 'month',
                'MULTIPLE_INTERVAL' => 1,
                'OFFSET_BEGINDATE_TYPE' => 'day',
                'OFFSET_BEGINDATE_VALUE' => 1,
                'OFFSET_CLOSEDATE_TYPE' => 'month',
                'OFFSET_CLOSEDATE_VALUE' => 2,
            ]
        ];
    }
}
