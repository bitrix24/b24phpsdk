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
use Bitrix24\SDK\Services\CRM\CallList\Service\CallList;
use Bitrix24\SDK\Tests\Integration\Fabric;
use PHPUnit\Framework\TestCase;

/**
 * Class BatchTest
 *
 * @package Bitrix24\SDK\Tests\Integration\Services\CRM\CallList\Service
 */
#[\PHPUnit\Framework\Attributes\CoversClass(\Bitrix24\SDK\Services\CRM\CallList\Service\Batch::class)]
class BatchTest extends TestCase
{
    protected CallList $callListService;
    
    
    protected function setUp(): void
    {
        $this->callListService = Fabric::getServiceBuilder()->getCRMScope()->callList();
    }

    /**
     * @throws BaseException
     * @throws TransportException
     */
    #[\PHPUnit\Framework\Attributes\TestDox('Batch get call lists')]
    public function testBatchList(): void
    {
        $callListNum = 60;
        $allContactIds = [];
        for ($i=0;$i<$callListNum;$i++) {
            $contactIds = $this->addContacts(2);
            $allContactIds = array_merge($allContactIds, $contactIds);
            $this->callListService->add('CONTACT', $contactIds);
        }
        $cnt = 0;
        foreach ($this->callListService->batch->list() as $item) {
            $cnt++;
        }

        self::assertGreaterThanOrEqual($callListNum, $cnt);
        
        $this->deleteContacts($allContactIds);
    }

    /**
     * @throws \Bitrix24\SDK\Core\Exceptions\BaseException
     */
    #[\PHPUnit\Framework\Attributes\TestDox('Batch add department')]
    public function testBatchAdd(): void
    {
        $callListNum = 60;
        $allContactIds = [];
        $callLists = [];
        for ($i=0;$i<$callListNum;$i++) {
            $contactIds = $this->addContacts(2);
            $allContactIds = array_merge($allContactIds, $contactIds);
            $callLists[] = [
                'ENTITY_TYPE' => 'CONTACT',
                'ENTITIES' => $contactIds
            ];
        }
        $cnt = 0;
        foreach ($this->callListService->batch->add($callLists) as $item) {
            $cnt++;
        }

        self::assertGreaterThanOrEqual($callListNum, $cnt);
        
        $this->deleteContacts($allContactIds);
    }
    
    /**
     * @throws \Bitrix24\SDK\Core\Exceptions\BaseException
     */
    #[\PHPUnit\Framework\Attributes\TestDox('Batch update departments')]
    public function testBatchUpdate(): void
    {
        $callListNum = 60;
        $allContactIds = [];
        $callListUpdates = [];
        $callListIds = [];
        for ($i=0;$i<$callListNum;$i++) {
            $contactIds = $this->addContacts(2);
            $allContactIds = array_merge($allContactIds, $contactIds);
            $callListIds[] = $this->callListService->add('CONTACT', $contactIds)->getId();
        }
        
        foreach ($callListIds as $callListId) {
            $contactIds = $this->addContacts(1);
            $allContactIds = array_merge($allContactIds, $contactIds);
            $callListUpdates[] = [
                'LIST_ID' => $callListId,
                'ENTITY_TYPE' => 'CONTACT',
                'ENTITIES' => $contactIds
            ];
        }
        
        $cnt = 0;
        foreach ($this->callListService->batch->update($callListUpdates) as $updateResult) {
            $cnt++;
            self::assertTrue($updateResult->isSuccess());
        }

        self::assertGreaterThanOrEqual($callListNum, $cnt);
        
        $this->deleteContacts($allContactIds);
    }
    
    protected function addContacts(int $num): array
    {
        $contactIds = [];
        $contacts = [];
        for ($i=1;$i<=$num;$i++) {
            $contacts[] = [
                'NAME' => 'Test contact #'.$i
            ];
        }
        foreach (Fabric::getServiceBuilder()->getCRMScope()->contact()->batch->add($contacts) as $item) {
            $contactIds[] = $item->getId();
        }
        
        return $contactIds;
    }
    
    protected function deleteContacts(array $contactIds): void
    {
        echo "Contacts: \n";
        print_r($contactIds);
        
        foreach (Fabric::getServiceBuilder()->getCRMScope()->contact()->batch->delete($contactIds) as $item) {
            self::assertTrue($item->isSuccess());
        }
    }

}
