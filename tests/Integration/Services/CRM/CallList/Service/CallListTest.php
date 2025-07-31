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

namespace Bitrix24\SDK\Tests\Integration\Services\CRM\CallList\Service;

use Bitrix24\SDK\Core\Exceptions\BaseException;
use Bitrix24\SDK\Core\Exceptions\TransportException;
use Bitrix24\SDK\Core;
use Bitrix24\SDK\Services\CRM\CallList\Result\CallListItemResult;
use Bitrix24\SDK\Services\CRM\CallList\Service\CallList;
use Bitrix24\SDK\Services\CRM\Contact\Service\Contact;
use Bitrix24\SDK\Tests\CustomAssertions\CustomBitrix24Assertions;
use Bitrix24\SDK\Tests\Integration\Fabric;
use PHPUnit\Framework\Attributes\CoversFunction;
use PHPUnit\Framework\Attributes\CoversMethod;
use PHPUnit\Framework\TestCase;

/**
 * Class CallListTest
 *
 * @package Bitrix24\SDK\Tests\Integration\Services\CRM\CallList\Service
 */
#[CoversMethod(CallList::class,'add')]
#[CoversMethod(CallList::class,'get')]
#[CoversMethod(CallList::class,'list')]
#[CoversMethod(CallList::class,'update')]
#[CoversMethod(CallList::class,'countByFilter')]
#[CoversMethod(CallList::class,'statusList')]
#[CoversMethod(CallList::class,'getItems')]
#[\PHPUnit\Framework\Attributes\CoversClass(\Bitrix24\SDK\Services\CRM\CallList\Service\CallList::class)]
class CallListTest extends TestCase
{
    use CustomBitrix24Assertions;
    
    protected CallList $callListService;
    
    protected array $contactIds = [];
    
    
    protected function setUp(): void
    {
        $this->callListService = Fabric::getServiceBuilder()->getCRMScope()->callList();
        $this->contactIds = [];
        $contacts = [
            ['NAME' => 'name-1'],
            ['NAME' => 'name-2'],
        ];
        foreach (Fabric::getServiceBuilder()->getCRMScope()->contact()->batch->add($contacts) as $item) {
            $this->contactIds[] = $item->getId();
        }
    }
    
    protected function tearDown(): void
    {
        foreach (Fabric::getServiceBuilder()->getCRMScope()->contact()->batch->delete($this->contactIds) as $item) {
            //
        }
    }

    /**
     * @throws BaseException
     * @throws TransportException
     */
    public function testAdd(): void
    {
        $listId = $this->callListService->add('CONTACT', $this->contactIds)->getId();
        self::assertGreaterThan(1, $listId);
    }

    /**
     * @throws BaseException
     * @throws TransportException
     */
    public function testGet(): void
    {
        $listId = $this->callListService->add('CONTACT', $this->contactIds)->getId();
        self::assertGreaterThan(
            1,
            $this->callListService->get($listId)->calllist()->ID
        );
    }
    
    /**
     * @throws BaseException
     * @throws TransportException
     */
    public function testList(): void
    {
        $listId = $this->callListService->add('CONTACT', $this->contactIds)->getId();
        self::assertGreaterThan(
            1,
            $this->callListService->list([], ['ID' => $listId])->getCallLists()[0]->ID
        );
    }
    
    /**
     * @throws BaseException
     * @throws TransportException
     */
    public function testStatusList(): void
    {
        $statuses = $this->callListService->statusList()->getStatuses();
        self::assertGreaterThan(
            1,
            count($statuses)
        );
    }
    
    /**
     * @throws BaseException
     * @throws TransportException
     */
    public function testGetItems(): void
    {
        $listId = $this->callListService->add('CONTACT', $this->contactIds)->getId();
        $items = $this->callListService->getItems($listId)->getItems();
        self::assertGreaterThan(
            1,
            count($items)
        );
    }

    /**
     * @throws BaseException
     * @throws TransportException
     */
    public function testUpdate(): void
    {
        $listId = $this->callListService->add('CONTACT', [ $this->contactIds[0] ])->getId();
        
        self::assertTrue($this->callListService->update($listId, 'CONTACT', $this->contactIds)->isSuccess());
    }

}
