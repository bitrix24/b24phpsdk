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

namespace Bitrix24\SDK\Tests\Integration\Services\CRM\Address\Service;

use Bitrix24\SDK\Core\Exceptions\BaseException;
use Bitrix24\SDK\Core\Exceptions\TransportException;
use Bitrix24\SDK\Core;
use Bitrix24\SDK\Services\CRM\Address\Service\Address;
use Bitrix24\SDK\Services\CRM\Address\Result\AddressItemResult;
use Bitrix24\SDK\Services\CRM\Company\Service\Company;
use Bitrix24\SDK\Services\CRM\Requisites\Service\Requisite;
use Bitrix24\SDK\Tests\Builders\Services\CRM\CompanyBuilder;
use Bitrix24\SDK\Tests\Builders\Services\CRM\RequisiteBuilder;
use Bitrix24\SDK\Services\ServiceBuilder;
use Bitrix24\SDK\Services\CRM\Enum\AddressType;
use Bitrix24\SDK\Services\CRM\Enum\OwnerType;

use Bitrix24\SDK\Services\CRM\Contact\Result\ContactItemResult;
use Bitrix24\SDK\Tests\CustomAssertions\CustomBitrix24Assertions;
use Bitrix24\SDK\Tests\Integration\Factory;
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
#[\PHPUnit\Framework\Attributes\CoversClass(\Bitrix24\SDK\Services\CRM\Address\Service\Address::class)]
class AddressTest extends TestCase
{
    use CustomBitrix24Assertions;

    protected ServiceBuilder $sb;

    protected Address $addressService;

    protected Company $companyService;

    protected Requisite $requisiteService;

    protected array   $addressTypes = [];

    protected array   $presets = [];

    protected function setUp(): void
    {
        $this->sb = Factory::getServiceBuilder();
        $this->addressService = $this->sb->getCRMScope()->address();
        $this->companyService = $this->sb->getCRMScope()->company();
        $this->requisiteService = $this->sb->getCRMScope()->requisite();
        $requisitePreset = $this->sb->getCRMScope()->requisitePreset();
        foreach ($requisitePreset->list()->getRequisitePresets() as $addressTypeFieldItemResult) {
            $this->presets[] = $addressTypeFieldItemResult->ID;
        }

        $enum = $this->sb->getCRMScope()->enum();
        foreach ($enum->addressType()->getItems() as $addressTypeFieldItemResult) {
            $this->addressTypes[] = $addressTypeFieldItemResult->ID;
        }
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
        $systemFields = array_filter($allFields, static fn($code): bool => in_array($code, $systemFieldsCodes, true), ARRAY_FILTER_USE_KEY);

        $this->assertBitrix24AllResultItemFieldsHasValidTypeAnnotation(
            $systemFields,
            AddressItemResult::class);
    }

    /**
     * @throws BaseException
     * @throws TransportException
     */
    public function testAdd(): void
    {
        [$companyId, $requisiteId] = $this->addCompanyAndRequisite();
        $fields = [
            'TYPE_ID' => $this->addressTypes[1],
            'ENTITY_TYPE_ID' => OwnerType::requisite->value,
            'ENTITY_ID' => $requisiteId,
            'ADDRESS_1' => '123, Test str.'
        ];
        self::assertEquals(
            1,
            $this->addressService->add($fields)->getCoreResponse()->getResponseData()->getResult()[0]
        );

        $this->companyService->delete($companyId);
    }

    /**
     * @throws BaseException
     * @throws TransportException
     */
    public function testDelete(): void
    {
        [$companyId, $requisiteId] = $this->addCompanyAndRequisite();
        $fields = [
            'TYPE_ID' => $this->addressTypes[1],
            'ENTITY_TYPE_ID' => OwnerType::requisite->value,
            'ENTITY_ID' => $requisiteId,
            'ADDRESS_1' => 'Test str.'
        ];

        $this->addressService->add($fields)->getCoreResponse()->getResponseData()->getResult();
        self::assertTrue(
            $this->addressService->delete(
                $fields['TYPE_ID'],
                $fields['ENTITY_TYPE_ID'],
                $fields['ENTITY_ID']
            )->isSuccess()
        );
        $this->companyService->delete($companyId);
    }

    /**
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
     */
    public function testList(): void
    {
        [$companyId, $requisiteId] = $this->addCompanyAndRequisite();
        $fields = [
            'TYPE_ID' => $this->addressTypes[1],
            'ENTITY_TYPE_ID' => OwnerType::requisite->value,
            'ENTITY_ID' => $requisiteId,
            'ADDRESS_1' => 'Test str.'
        ];
        $this->addressService->add($fields);
        self::assertGreaterThanOrEqual(1, $this->addressService->list([], [], ['TYPE_ID'])->getAddresses());

        $this->companyService->delete($companyId);
    }

    /**
     * @throws BaseException
     * @throws TransportException
     */
    public function testUpdate(): void
    {
        [$companyId, $requisiteId] = $this->addCompanyAndRequisite();
        $this->addressService->add([
            'TYPE_ID' => $this->addressTypes[1],
            'ENTITY_TYPE_ID' => OwnerType::requisite->value,
            'ENTITY_ID' => $requisiteId,
            'ADDRESS_1' => '123, Test str.'
        ]);
        $newAddress = 'Updated 123, Test str.';
        $newFields = [
            'TYPE_ID' => $this->addressTypes[1],
            'ENTITY_TYPE_ID' => OwnerType::requisite->value,
            'ENTITY_ID' => $requisiteId,
            'ADDRESS_1' => $newAddress
        ];

        self::assertTrue($this->addressService->update($newFields)->isSuccess());
        $filter = [
            'TYPE_ID' => $this->addressTypes[1],
            'ENTITY_TYPE_ID' => OwnerType::requisite->value,
            'ENTITY_ID' => $requisiteId,
        ];
        $response = $this->addressService->list(
            [],
            $filter,
            ['ADDRESS_1']
        )->getCoreResponse()->getResponseData()->getResult()[0]['ADDRESS_1'];
        self::assertEquals($newAddress, $response);

        $this->companyService->delete($companyId);
    }

    /**
     * @throws \Bitrix24\SDK\Core\Exceptions\BaseException
     * @throws \Bitrix24\SDK\Core\Exceptions\TransportException
     */
    /*
    // restApi bug. Issue: https://github.com/bitrix24/b24phpsdk/issues/144
    public function testCountByFilter(): void
    {
        $before = $this->addressService->countByFilter();

        [$companyId, $requisiteId] = $this->addCompanyAndRequisite();
        $fields = [
            'TYPE_ID' => $this->addressTypes[1],
            'ENTITY_TYPE_ID' => OwnerType::requisite->value,
            'ENTITY_ID' => $requisiteId,
            'ADDRESS_1' => '0, Test str.'
        ];
        $items = [];
        foreach ($this->addressTypes as $addressType) {
            $stepFields = $fields;
            $stepFields['TYPE_ID'] = $addressType;
            $stepFields['ADDRESS_1'] = $addressType . $stepFields['ADDRESS_1'];
            $items[] = $stepFields;
        }

        $cnt = 0;
        foreach ($this->addressService->batch->add($items) as $item) {
            $cnt++;
        }

        self::assertEquals(count($items), $cnt);

        $after = $this->addressService->countByFilter();

        $this->assertEquals($before + count($this->addressTypes), $after);

        $this->companyService->delete($companyId);
    }
    */

    protected function addCompanyAndRequisite(): array {
        $companyId = $this->companyService->add((new CompanyBuilder())->build())->getId();
        $requisiteId = $this->requisiteService->add(
            $companyId,
            4,
            $this->presets[0],
            'Test requisite',
            (new RequisiteBuilder(OwnerType::company->value, $companyId, $this->presets[0]))->build()
        )->getId();

        return [$companyId, $requisiteId];
    }
}