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

namespace Bitrix24\SDK\Tests\Integration\Services\CRM\Address\Service;

use Bitrix24\SDK\Core\Exceptions\BaseException;
use Bitrix24\SDK\Core\Exceptions\TransportException;
use Bitrix24\SDK\Core;
use Bitrix24\SDK\Services\CRM\Address\Service\Address;
use Bitrix24\SDK\Services\CRM\Address\Result\AddressItemResult;
use Bitrix24\SDK\Services\ServiceBuilder;
use Bitrix24\SDK\Tests\Builders\Services\CRM\CompanyBuilder;
use Bitrix24\SDK\Services\CRM\Enum\AddressType;
use Bitrix24\SDK\Services\CRM\Enum\OwnerType;

use Bitrix24\SDK\Services\CRM\Contact\Result\ContactItemResult;
use Bitrix24\SDK\Tests\CustomAssertions\CustomBitrix24Assertions;
use Bitrix24\SDK\Tests\Integration\Fabric;
use PHPUnit\Framework\Attributes\CoversFunction;
use PHPUnit\Framework\Attributes\CoversMethod;
use PHPUnit\Framework\TestCase;

/**
 * Class LeadTest
 *
 * @package Bitrix24\SDK\Tests\Integration\Services\CRM\Address\Service
 */
#[CoversMethod(Address::class,'add')]
#[CoversMethod(Address::class,'delete')]
#[CoversMethod(Address::class,'list')]
#[CoversMethod(Address::class,'fields')]
#[CoversMethod(Address::class,'update')]
class AddressTest extends TestCase
{
    use CustomBitrix24Assertions;
    
    protected ServiceBuilder $sb;
    protected Address $addressService;
    
    public function setUp(): void
    {
        $this->sb = Fabric::getServiceBuilder();
        $this->addressService = Fabric::getServiceBuilder()->getCRMScope()->address();
    }

    public function testAllSystemFieldsAnnotated(): void
    {
        $propListFromApi = (new Core\Fields\FieldsFilter())->filterSystemFields(array_keys($this->addressService->fields()->getFieldsDescription()));
        $this->assertBitrix24AllResultItemFieldsAnnotated($propListFromApi, AddressItemResult::class);
    }

    public function testAllSystemFieldsHasValidTypeAnnotation():void
    {
        $allFields = $this->addressService->fields()->getFieldsDescription();
        $systemFieldsCodes = (new Core\Fields\FieldsFilter())->filterSystemFields(array_keys($allFields));
        $systemFields = array_filter($allFields, static function ($code) use ($systemFieldsCodes) {
            return in_array($code, $systemFieldsCodes, true);
        }, ARRAY_FILTER_USE_KEY);

        $this->assertBitrix24AllResultItemFieldsHasValidTypeAnnotation(
            $systemFields,
            AddressItemResult::class);
    }

    /**
     * @throws BaseException
     * @throws TransportException
     * @covers Address::add
     */
    public function testAdd(): void
    {
        $companyId = $this->sb->getCRMScope()->company()->add((new CompanyBuilder())->build())->getId();
        self::assertEquals(1, $this->addressService->add(
            [
                'TYPE_ID' => AddressType::actual->value(),
                'ENTITY_TYPE_ID' => OwnerType::company->value(),
                'ENTITY_ID' => $companyId,
            ])->getCoreResponse()->getResponseData()->getResult()[0]);
    }

    /**
     * @throws BaseException
     * @throws TransportException
     * @covers Address::delete
     */
    public function testDelete(): void
    {
        self::assertTrue($this->addressService->delete($this->addressService->add(['TITLE' => 'test lead'])->getId())->isSuccess());
    }

    /**
     * @covers Address::fields
     * @throws BaseException
     * @throws TransportException
     */
    public function testFields(): void
    {
        self::assertIsArray($this->addressService->fields()->getFieldsDescription());
    }

    /**
     * @throws BaseException
     * @throws TransportException
     * @covers Address::list
     */
    public function testList(): void
    {
        $companyId = $this->sb->getCRMScope()->company()->add((new CompanyBuilder())->build())->getId();
        $this->addressService->add([
            'TYPE_ID' => AddressType::actual->value(),
            'ENTITY_TYPE_ID' => OwnerType::company->value(),
            'ENTITY_ID' => $companyId,
        ]);
        self::assertGreaterThanOrEqual(1, $this->addressService->list([], [], ['TYPE_ID'])->getAddresses());
    }

    /**
     * @throws BaseException
     * @throws TransportException
     * @covers Address::update
     */
    public function testUpdate(): void
    {
        $deal = $this->addressService->add(['TITLE' => 'test lead']);
        $newTitle = 'test2';

        self::assertTrue($this->addressService->update($deal->getId(), ['TITLE' => $newTitle], [])->isSuccess());
        self::assertEquals($newTitle, $this->addressService->get($deal->getId())->lead()->TITLE);
    }

    /**
     * @throws \Bitrix24\SDK\Core\Exceptions\BaseException
     * @throws \Bitrix24\SDK\Core\Exceptions\TransportException
     * @covers \Bitrix24\SDK\Services\CRM\Deal\Service\Deal::countByFilter
     */
    public function testCountByFilter(): void
    {
        $before = $this->addressService->countByFilter();

        $newItemsCount = 60;
        $items = [];
        for ($i = 1; $i <= $newItemsCount; $i++) {
            $items[] = ['TITLE' => 'TITLE-' . $i];
        }
        $cnt = 0;
        foreach ($this->addressService->batch->add($items) as $item) {
            $cnt++;
        }
        self::assertEquals(count($items), $cnt);

        $after = $this->addressService->countByFilter();

        $this->assertEquals($before + $newItemsCount, $after);
    }
}