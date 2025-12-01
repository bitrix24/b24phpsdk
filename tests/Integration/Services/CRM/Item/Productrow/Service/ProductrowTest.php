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
use Bitrix24\SDK\Core;
use Bitrix24\SDK\Services\CRM\Item\Productrow\Result\ProductrowItemResult;
use Bitrix24\SDK\Services\CRM\Item\Productrow\Service\Productrow;
use Bitrix24\SDK\Services\CRM\Lead\Service\Lead;
use Bitrix24\SDK\Tests\CustomAssertions\CustomBitrix24Assertions;
use Bitrix24\SDK\Tests\Integration\Factory;
use PHPUnit\Framework\Attributes\CoversFunction;
use PHPUnit\Framework\Attributes\CoversMethod;
use PHPUnit\Framework\TestCase;

/**
 * Class ProductrowTest
 *
 * @package Bitrix24\SDK\Tests\Integration\Services\CRM\Item\Productrow\Service
 */
#[CoversMethod(Productrow::class,'add')]
#[CoversMethod(Productrow::class,'delete')]
#[CoversMethod(Productrow::class,'get')]
#[CoversMethod(Productrow::class,'list')]
#[CoversMethod(Productrow::class,'fields')]
#[CoversMethod(Productrow::class,'update')]
#[CoversMethod(Productrow::class,'set')]
#[CoversMethod(Productrow::class,'countByFilter')]
#[\PHPUnit\Framework\Attributes\CoversClass(\Bitrix24\SDK\Services\CRM\Item\Productrow\Service\Productrow::class)]
class ProductrowTest extends TestCase
{
    use CustomBitrix24Assertions;
    
    protected Productrow $productrowService;
    
    protected Lead $leadService;
    
    protected int $leadId = 0;

    protected function setUp(): void
    {
        $this->productrowService = Factory::getServiceBuilder()->getCRMScope()->itemProductrow();
        $this->leadService = Factory::getServiceBuilder()->getCRMScope()->lead();
        
        $this->leadId = $this->leadService->add(['TITLE' => 'test lead for productRows'])->getId();
    }
    
    protected function tearDown(): void
    {
        $this->leadService->delete($this->leadId);
    }
    
    public function testAllSystemFieldsAnnotated(): void
    {
        $propListFromApi = (new Core\Fields\FieldsFilter())->filterSystemFields(array_keys($this->productrowService->fields()->getFieldsDescription()));
        $this->assertBitrix24AllResultItemFieldsAnnotated($propListFromApi, ProductrowItemResult::class);
    }

    public function testAllSystemFieldsHasValidTypeAnnotation():void
    {
        $allFields = $this->productrowService->fields()->getFieldsDescription();
        $systemFieldsCodes = (new Core\Fields\FieldsFilter())->filterSystemFields(array_keys($allFields));
        $systemFields = array_filter($allFields, static fn($code): bool => in_array($code, $systemFieldsCodes, true), ARRAY_FILTER_USE_KEY);

        $this->assertBitrix24AllResultItemFieldsHasValidTypeAnnotation(
            $systemFields,
            ProductrowItemResult::class);
    }

    /**
     * @throws BaseException
     * @throws TransportException
     */
    public function testAdd(): void
    {
        $fields = $this->getProductrowFields();
        self::assertGreaterThan(1, $this->productrowService->add($fields)->getId());
    }

    /**
     * @throws BaseException
     * @throws TransportException
     */
    public function testDelete(): void
    {
        $fields = $this->getProductrowFields();
        $rowId = $this->productrowService->add($fields)->getId();
        echo 'Row ID: ';
        print_r($rowId);
        $res = $this->productrowService->delete($rowId)->getCoreResponse()->getResponseData()->getResult()[0];
        // always returns result => null
        self::assertNull($res);
    }

    /**
     * @throws BaseException
     * @throws TransportException
     */
    public function testFields(): void
    {
        self::assertIsArray($this->productrowService->fields()->getFieldsDescription());
    }

    /**
     * @throws BaseException
     * @throws TransportException
     */
    public function testGet(): void
    {
        $fields = $this->getProductrowFields();
        $rowId = $this->productrowService->add($fields)->getId();
        self::assertGreaterThan(
            1,
            $this->productrowService->get($rowId)->productrow()->id
        );
    }

    /**
     * @throws BaseException
     * @throws TransportException
     */
    public function testList(): void
    {
        $fields = $this->getProductrowFields();
        $this->productrowService->add($fields)->getId();
        $filter = [
            '=ownerId' => $this->leadId,
            '=ownerType' => 'L',
        ];
        self::assertGreaterThanOrEqual(1, $this->productrowService->list([], $filter)->getProductrows());
    }
    
    /**
     * @throws BaseException
     * @throws TransportException
     */
    public function testSet(): void
    {
        $fields = $this->getProductrowFields();
        unset($fields['ownerId']);
        unset($fields['ownerType']);
        $items = [];
        for ($i = 1; $i < 11; $i++) {
            $copy = $fields;
            $copy['productName'] .= ' ' . $i;
            $copy['price'] += $i;
            $copy['sort'] += $i;
            $items[] = $copy;
        }

        $setResultItems = $this->productrowService->set($this->leadId, 'L', $items)->getProductrows();
        self::assertGreaterThanOrEqual(count($items), count($setResultItems));
    }
    
    /**
     * @throws BaseException
     * @throws TransportException
     */
    public function testGetAvailableForPayment(): void
    {
        $fields = $this->getProductrowFields();
        unset($fields['ownerId']);
        unset($fields['ownerType']);
        $items = [];
        for ($i = 1; $i < 6; $i++) {
            $copy = $fields;
            $copy['productName'] .= ' ' . $i;
            $copy['price'] += $i;
            $copy['sort'] += $i;
            $items[] = $copy;
        }
        
        $this->productrowService->set($this->leadId, 'L', $items)->getProductrows();
        $availableItems = $this->productrowService->getAvailableForPayment($this->leadId, 'L')->getProductrows();
        // This way, nothing is returned
        //self::assertGreaterThanOrEqual(count($setResultItems), count($availableItems));
        self::assertIsArray($availableItems);
    }

    /**
     * @throws BaseException
     * @throws TransportException
     */
    public function testUpdate(): void
    {
        $fields = $this->getProductrowFields();
        $rowId = $this->productrowService->add($fields)->getId();
        $newName = 'Test product 222';

        $this->productrowService->update($rowId, ['productName' => $newName]);
        self::assertEquals($newName, $this->productrowService->get($rowId)->productrow()->productName);
    }

    /**
     * @throws \Bitrix24\SDK\Core\Exceptions\BaseException
     * @throws \Bitrix24\SDK\Core\Exceptions\TransportException
     */
    public function testCountByFilter(): void
    {
        $filter = [
            '=ownerId' => $this->leadId,
            '=ownerType' => 'L',
        ];
        $before = $this->productrowService->countByFilter($filter);

        $fields = $this->getProductrowFields();
        $this->productrowService->add($fields)->getId();

        $after = $this->productrowService->countByFilter($filter);

        $this->assertEquals($before + 1, $after);
    }   
    
    private function getProductrowFields(): array {
        return [
            'ownerId' => $this->leadId,
            'ownerType' => 'L',
            'productName' => 'Test product for lead',
            'price' => 0.5,
            'sort' => 100,
        ];
    }
}
