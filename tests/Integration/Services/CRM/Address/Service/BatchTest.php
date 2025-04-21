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
use Bitrix24\SDK\Services\ServiceBuilder;
use Bitrix24\SDK\Services\CRM\Address\Service\Address;
use Bitrix24\SDK\Services\CRM\Enum\AddressType;
use Bitrix24\SDK\Services\CRM\Company\Service\Company;
use Bitrix24\SDK\Services\CRM\Requisites\Service\Requisite;
use Bitrix24\SDK\Tests\Builders\Services\CRM\CompanyBuilder;
use Bitrix24\SDK\Tests\Builders\Services\CRM\RequisiteBuilder;
use Bitrix24\SDK\Services\CRM\Enum\OwnerType;
use Bitrix24\SDK\Tests\Integration\Fabric;
use PHPUnit\Framework\TestCase;

/**
 * Class BatchTest
 *
 * @package Bitrix24\SDK\Tests\Integration\Services\CRM\Address\Service
 */
class BatchTest extends TestCase
{
    protected ServiceBuilder $sb;
    protected Address $addressService;
    protected Company $companyService;
    protected Requisite $requisiteService;
    protected array   $addressTypes = [];
    protected array   $presets = [];
    
    public function setUp(): void
    {
        $this->sb = Fabric::getServiceBuilder();
        $this->addressService = $this->sb->getCRMScope()->address();
        $this->companyService = $this->sb->getCRMScope()->company();
        $this->requisiteService = $this->sb->getCRMScope()->requisite();
        $requisitePreset = $this->sb->getCRMScope()->requisitePreset();
        foreach ($requisitePreset->list()->getRequisitePresets() as $item) {
            $this->presets[] = $item->ID;
        }
        $enum = Fabric::getServiceBuilder()->getCRMScope()->enum();
        foreach ($enum->addressType()->getItems() as $item) {
            $this->addressTypes[] = $item->ID;
        }
    }

    /**
     * @testdox Batch list addresses
     * @covers  \Bitrix24\SDK\Services\CRM\Address\Service\Batch::list()
     * @throws BaseException
     * @throws TransportException
     */
    public function testBatchList(): void
    {
        list($companyId, $requisiteId) = $this->addCompanyAndRequisite();
        $fields = [
            'TYPE_ID' => $this->addressTypes[1],
            'ENTITY_TYPE_ID' => OwnerType::requisite->value,
            'ENTITY_ID' => $requisiteId,
            'ADDRESS_1' => '123, Test str.'
        ];
        $this->addressService->add($fields);
        $cnt = 0;
        $list = $this->addressService->batch->list([], ['TYPE_ID' => $this->addressTypes[1]], ['TYPE_ID'], 1);
        foreach ($list as $item) {
            $cnt++;
        }
        self::assertGreaterThanOrEqual(1, $cnt);
        
        $this->companyService->delete($companyId);
    }

    /**
     * @testdox Batch add address
     * @covers  \Bitrix24\SDK\Services\CRM\Address\Service\Batch::add()
     * @throws \Bitrix24\SDK\Core\Exceptions\BaseException
     */
    public function testBatchAdd(): void
    {
        list($companyId, $requisiteId) = $this->addCompanyAndRequisite();
        $fields = [
            'TYPE_ID' => 0,
            'ENTITY_TYPE_ID' => OwnerType::requisite->value,
            'ENTITY_ID' => $requisiteId,
            'ADDRESS_1' => '0, Test str.'
        ];
        
        $items = [];
        foreach ($this->addressTypes as $typeId) {
            $stepFields = $fields;
            $stepFields['TYPE_ID'] = $typeId;
            $stepFields['ADDRESS_1'] = $typeId . $stepFields['ADDRESS_1'];
            $items[] = $stepFields;
        }
        
        $cnt = 0;
        foreach ($this->addressService->batch->add($items) as $item) {
            $cnt++;
        }
        self::assertEquals(count($items), $cnt);

        $this->companyService->delete($companyId);
    }

    /**
     * @testdox Batch delete address
     * @covers  \Bitrix24\SDK\Services\CRM\Address\Service\Batch::add()
     * @throws \Bitrix24\SDK\Core\Exceptions\BaseException
     */
    public function testBatchDelete(): void
    {
        $items = [];
        list($companyId, $requisiteId) = $this->addCompanyAndRequisite();
        $fields = [
            'TYPE_ID' => 0,
            'ENTITY_TYPE_ID' => OwnerType::requisite->value,
            'ENTITY_ID' => $requisiteId,
            'ADDRESS_1' => '0, Test str.'
        ];
        foreach ($this->addressTypes as $typeId) {
            $stepFields = $fields;
            $stepFields['TYPE_ID'] = $typeId;
            $stepFields['ADDRESS_1'] = $typeId . $stepFields['ADDRESS_1'];
            $items[] = $stepFields;
        }
        
        $cnt = 0;
        foreach ($this->addressService->batch->add($items) as $item) {
            $cnt++;
        }
        self::assertEquals(count($items), $cnt);

        $cnt = 0;
        $items = [];
        foreach ($this->addressTypes as $typeId) {
            $stepFields = $fields;
            $stepFields['TYPE_ID'] = $typeId;
            unset($stepFields['ADDRESS_1']);
            $items[] = $stepFields;
        }
        foreach ($this->addressService->batch->delete($items) as $cnt => $deleteResult) {
            $cnt++;
        }
        self::assertEquals(count($items), $cnt);
        
        $this->companyService->delete($companyId);
    }
    
    /**
     * @testdox Batch update address
     * @covers  \Bitrix24\SDK\Services\CRM\Address\Service\Batch::update()
     * @throws \Bitrix24\SDK\Core\Exceptions\BaseException
     */
    public function testBatchUpdate(): void
    {
        $items = [];
        list($companyId, $requisiteId) = $this->addCompanyAndRequisite();
        $fields = [
            'TYPE_ID' => 0,
            'ENTITY_TYPE_ID' => OwnerType::requisite->value,
            'ENTITY_ID' => $requisiteId,
            'ADDRESS_1' => '0, Test str.'
        ];
        foreach ($this->addressTypes as $typeId) {
            $stepFields = $fields;
            $stepFields['TYPE_ID'] = $typeId;
            $stepFields['ADDRESS_1'] = $typeId . $stepFields['ADDRESS_1'];
            $items[] = $stepFields;
        }
        
        $cnt = 0;
        foreach ($this->addressService->batch->add($items) as $item) {
            $cnt++;
        }
        self::assertEquals(count($items), $cnt);

        $cnt = 0;
        $items = [];
        $newAddress1 = 'Updated address 1';
        foreach ($this->addressTypes as $typeId) {
            $stepFields = $fields;
            $stepFields['TYPE_ID'] = $typeId;
            $stepFields['ADDRESS_1'] = $newAddress1;
            $stepFields = ['fields' => $stepFields];
            
            $items[] = $stepFields;
        }
        foreach ($this->addressService->batch->update($items) as $cnt => $updateResult) {
            $cnt++;
        }
        self::assertEquals(count($items), $cnt);
        
        $this->companyService->delete($companyId);
    }
    
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