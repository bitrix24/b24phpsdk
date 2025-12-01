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
use Bitrix24\SDK\Tests\Integration\Factory;
use PHPUnit\Framework\TestCase;

/**
 * Class DealRecurringTest
 *
 * @package Bitrix24\SDK\Tests\Integration\Services\CRM\Deals\Service
 */
#[\PHPUnit\Framework\Attributes\CoversClass(\Bitrix24\SDK\Services\CRM\Deal\Service\DealRecurring::class)]
class DealRecurringTest extends TestCase
{
    protected DealRecurring $dealRecurring;

    protected Deal $dealService;

    protected function setUp(): void
    {
        $this->dealRecurring = Factory::getServiceBuilder()->getCRMScope()->dealRecurring();
        $this->dealService = Factory::getServiceBuilder()->getCRMScope()->deal();

    }

    /**
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

        $recurring = $this->dealRecurring->get($recurringId)->recurring();
        $this->dealService->delete(intval($recurring->DEAL_ID));
        $this->dealService->delete(intval($recurring->BASED_ID));
    }

    /**
     * @throws BaseException
     * @throws TransportException
     */
    public function testFields(): void
    {
        $this::assertIsArray($this->dealRecurring->fields()->getFieldsDescription());
    }

    /**
     * @throws BaseException
     * @throws TransportException
     */
    public function testDealRecurringGet(): void
    {
        $dealId = $this->dealService->add(['TITLE' => 'test recurring deal'])->getId();
        $newRecurring = $this->getRecurringFields($dealId);
        $newRecurringId = $this->dealRecurring->add($newRecurring)->getId();
        $recurring = $this->dealRecurring->get($newRecurringId)->recurring();

        $this::assertEquals($newRecurring['DEAL_ID'], $recurring->BASED_ID);

        $recurring = $this->dealRecurring->get($newRecurringId)->recurring();
        $this->dealService->delete(intval($recurring->DEAL_ID));
        $this->dealService->delete(intval($recurring->BASED_ID));
    }

    /**
     *
     * @throws BaseException
     * @throws TransportException
     */
    public function testList(): void
    {
        $dealId = $this->dealService->add(['TITLE' => 'test recurring deal'])->getId();
        $newRecurringId = $this->dealRecurring->add($this->getRecurringFields($dealId))->getId();
        $dealRecurringsResult = $this->dealRecurring->list([], [], [], 0);

        $this::assertGreaterThanOrEqual(1, count($dealRecurringsResult->getDealRecurrings()));

        $recurring = $this->dealRecurring->get($newRecurringId)->recurring();
        $this->dealService->delete(intval($recurring->DEAL_ID));
        $this->dealService->delete(intval($recurring->BASED_ID));
    }

    /**
     * @throws BaseException
     * @throws TransportException
     */
    public function testExpose(): void
    {
        $dealId = $this->dealService->add(['TITLE' => 'test recurring deal'])->getId();
        $newRecurringId = $this->dealRecurring->add($this->getRecurringFields($dealId))->getId();
        $dealRecurringExposeResult = $this->dealRecurring->expose($newRecurringId);

        $this::assertGreaterThan(1, $dealRecurringExposeResult->getDealId());

        $recurring = $this->dealRecurring->get($newRecurringId)->recurring();
        $this->dealService->delete(intval($recurring->DEAL_ID));
        $this->dealService->delete(intval($recurring->BASED_ID));
        $this->dealService->delete($dealRecurringExposeResult->getDealId());
    }

    /**
     * @throws BaseException
     * @throws TransportException
     */
    public function testUpdate(): void
    {
        $dealId = $this->dealService->add(['TITLE' => 'test recurring deal'])->getId();
        $newRecurring = $this->getRecurringFields($dealId);
        $newRecurringId = $this->dealRecurring->add($newRecurring)->getId();
        $newInterval = 2;
        $newRecurring['PARAMS']['MULTIPLE_INTERVAL'] = $newInterval;
        $this::assertTrue($this->dealRecurring->update($newRecurringId, [ 'PARAMS' => $newRecurring['PARAMS'] ])->isSuccess());
        $recurring = $this->dealRecurring->get($newRecurringId)->recurring();
        $this::assertEquals($newInterval, $recurring->PARAMS['MULTIPLE_INTERVAL']);

        $this->dealService->delete(intval($recurring->DEAL_ID));
        $this->dealService->delete(intval($recurring->BASED_ID));
    }

    protected function getRecurringFields(int $dealId): array {
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
