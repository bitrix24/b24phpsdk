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

namespace Bitrix24\SDK\Tests\Integration\Services\CRM\Item\Productrow\Service;

use Bitrix24\SDK\Core\Exceptions\BaseException;
use Bitrix24\SDK\Core\Exceptions\TransportException;
use Bitrix24\SDK\Services\CRM\Item\Productrow\Service\Productrow;
use Bitrix24\SDK\Services\CRM\Lead\Service\Lead;
use Bitrix24\SDK\Tests\Integration\Factory;
use PHPUnit\Framework\TestCase;

/**
 * Class BatchTest
 *
 * @package Bitrix24\SDK\Tests\Integration\Services\CRM\Item\Productrow\Service
 */
#[\PHPUnit\Framework\Attributes\CoversClass(\Bitrix24\SDK\Services\CRM\Item\Productrow\Service\Batch::class)]
class BatchTest extends TestCase
{
    protected Productrow $productrowService;
    
    protected Lead $leadService;
    
    protected int $leadId = 0;

    #[\Override]
    protected function setUp(): void
    {
        $this->productrowService = Factory::getServiceBuilder()->getCRMScope()->itemProductrow();
        $this->leadService = Factory::getServiceBuilder()->getCRMScope()->lead();
        
        $this->leadId = $this->leadService->add(['TITLE' => 'test lead for productRows'])->getId();
    }
    
    #[\Override]
    protected function tearDown(): void
    {
        $this->leadService->delete($this->leadId);
    }
    
    /**
     * @throws BaseException
     * @throws TransportException
     */
    #[\PHPUnit\Framework\Attributes\TestDox('Batch list productrows')]
    public function testBatchList(): void
    {
        $fields = $this->getProductrowFields();
        $rowId = $this->productrowService->add($fields)->getId();
        
        $filter = [
            'id' => $rowId,
            '=ownerId' => $this->leadId,
            '=ownerType' => 'L',
        ];
        $cnt = 0;
        foreach ($this->productrowService->batch->list([], $filter, 1) as $item) {
            $cnt++;
        }

        self::assertGreaterThanOrEqual(1, $cnt);
    }

    /**
     * @throws \Bitrix24\SDK\Core\Exceptions\BaseException
     */
    #[\PHPUnit\Framework\Attributes\TestDox('Batch add lead')]
    public function testBatchAdd(): void
    {
        $fields = $this->getProductrowFields();
        $items = [];
        for ($i = 1; $i < 11; $i++) {
            $copy = $fields;
            $copy['productName'] .= ' ' . $i;
            $copy['price'] += $i;
            $items[] = $copy;
        }

        $cnt = 0;
        foreach ($this->productrowService->batch->add($items) as $item) {
            $cnt++;
        }

        self::assertEquals(count($items), $cnt);
    }

    /**
     * @throws \Bitrix24\SDK\Core\Exceptions\BaseException
     */
    #[\PHPUnit\Framework\Attributes\TestDox('Batch delete productrows')]
    public function testBatchDelete(): void
    {
        $fields = $this->getProductrowFields();
        $items = [];
        for ($i = 1; $i < 11; $i++) {
            $copy = $fields;
            $copy['productName'] .= ' ' . $i;
            $copy['price'] += $i;
            $items[] = $copy;
        }

        $cnt = 0;
        $prodId = [];
        foreach ($this->productrowService->batch->add($items) as $item) {
            $cnt++;
            $prodId[] = $item->id;
        }

        $cnt = 0;
        foreach ($this->productrowService->batch->delete($prodId) as $cnt => $deleteResult) {
            $cnt++;
        }

        self::assertEquals(count($items), $cnt);
    }
    
    private function getProductrowFields(): array {
        return [
            'ownerId' => $this->leadId,
            'ownerType' => 'L',
            'productName' => 'Test product for lead',
            'price' => 0.5,
        ];
    }
}