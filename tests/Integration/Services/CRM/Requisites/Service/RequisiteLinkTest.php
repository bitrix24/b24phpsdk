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

namespace Bitrix24\SDK\Tests\Integration\Services\CRM\Requisites\Service;

use Bitrix24\SDK\Core\Exceptions\BaseException;
use Bitrix24\SDK\Core\Exceptions\TransportException;
use Bitrix24\SDK\Core;
use Bitrix24\SDK\Services\CRM\Requisites\Result\RequisiteLinkItemResult;
use Bitrix24\SDK\Services\CRM\Requisites\Service\RequisiteLink;
use Bitrix24\SDK\Services\CRM\Company\Service\Company;
use Bitrix24\SDK\Services\CRM\Requisites\Service\Requisite;
use Bitrix24\SDK\Services\ServiceBuilder;
use Bitrix24\SDK\Tests\Builders\Services\CRM\CompanyBuilder;
use Bitrix24\SDK\Tests\Builders\Services\CRM\RequisiteBuilder;

use Bitrix24\SDK\Tests\CustomAssertions\CustomBitrix24Assertions;
use Bitrix24\SDK\Tests\Integration\Fabric;
use PHPUnit\Framework\Attributes\CoversFunction;
use PHPUnit\Framework\Attributes\CoversMethod;
use PHPUnit\Framework\TestCase;

/**
 * Class RequisiteLinkTest
 *
 * @package Bitrix24\SDK\Tests\Integration\Services\CRM\Requisite\Service
 */
#[CoversMethod(RequisiteLink::class,'add')]
#[CoversMethod(RequisiteLink::class,'delete')]
#[CoversMethod(RequisiteLink::class,'get')]
#[CoversMethod(RequisiteLink::class,'list')]
#[CoversMethod(RequisiteLink::class,'fields')]
#[CoversMethod(RequisiteLink::class,'update')]
#[CoversMethod(RequisiteLink::class,'countByFilter')]
#[\PHPUnit\Framework\Attributes\CoversClass(\Bitrix24\SDK\Services\CRM\Requisites\Service\RequisiteLink::class)]
class RequisiteLinkTest extends TestCase
{
    use CustomBitrix24Assertions;
    
    const COMPANY_OWNER_TYPE_ID = 4;
    
    protected ServiceBuilder $sb;
    protected RequisiteLink $linkService;
    protected Company $companyService;
    protected Requisite $requisiteService;
    protected array   $presets = [];
    private array $createdCompanies = [];
    
    protected function setUp(): void
    {
        $this->sb = Fabric::getServiceBuilder();
        $this->companyService = $this->sb->getCRMScope()->company();
        $this->requisiteService = $this->sb->getCRMScope()->requisite();
        $this->linkService = $this->sb->getCRMScope()->requisiteBankdetail();
        $requisitePreset = $this->sb->getCRMScope()->requisitePreset();
        foreach ($requisitePreset->list()->getRequisitePresets() as $presetItemResult) {
            $this->presets[] = $presetItemResult->ID;
        }
    }
    
    protected function tearDown(): void
    {
        foreach ($this->companyService->batch->delete($this->createdCompanies) as $result) {
            // ###
        }
    }

    public function testAllSystemFieldsAnnotated(): void
    {
        $fieldDescriptions = $this->linkService->fields()->getFieldsDescription();
        echo "Fields \n";
        print_r($fieldDescriptions);
        
        $propListFromApi = (new Core\Fields\FieldsFilter())->filterSystemFields(array_keys($fieldDescriptions));
        $this->assertBitrix24AllResultItemFieldsAnnotated($propListFromApi, RequisiteLinkItemResult::class);
    }

    public function testAllSystemFieldsHasValidTypeAnnotation():void
    {
        $allFields = $this->linkService->fields()->getFieldsDescription();
        $systemFieldsCodes = (new Core\Fields\FieldsFilter())->filterSystemFields(array_keys($allFields));
        $systemFields = array_filter($allFields, static fn($code): bool => in_array($code, $systemFieldsCodes, true), ARRAY_FILTER_USE_KEY);

        $this->assertBitrix24AllResultItemFieldsHasValidTypeAnnotation(
            $systemFields,
            RequisiteLinkItemResult::class);
    }

    /**
     * @throws BaseException
     * @throws TransportException
     */
    public function testAdd(): void
    {
        $presetId = $this->presets[0];
        list($companyId, $requisiteId) = $this->addCompanyAndRequisite($presetId);
        $this->createdCompanies[] = $companyId;
        $bankRequisite = $this->linkService->add([
            'ENTITY_ID' => $requisiteId,
            'NAME' => 'Test bank requisite'
        ]);
        self::assertGreaterThan(1, $bankRequisite->getId());
    }

    /**
     * @throws BaseException
     * @throws TransportException
     */
    public function testDelete(): void
    {
        $presetId = $this->presets[0];
        list($companyId, $requisiteId) = $this->addCompanyAndRequisite($presetId);
        $this->createdCompanies[] = $companyId;
        $bankRequisite = $this->linkService->add([
            'ENTITY_ID' => $requisiteId,
            'NAME' => 'Test bank requisite'
        ]);
        self::assertTrue($this->linkService->delete($bankRequisite->getId())->isSuccess());
    }

    /**
     * @throws BaseException
     * @throws TransportException
     */
    public function testFields(): void
    {
        self::assertIsArray($this->linkService->fields()->getFieldsDescription());
    }

    /**
     * @throws BaseException
     * @throws TransportException
     */
    public function testGet(): void
    {
        $presetId = $this->presets[0];
        list($companyId, $requisiteId) = $this->addCompanyAndRequisite($presetId);
        $this->createdCompanies[] = $companyId;
        $bankRequisite = $this->linkService->add([
            'ENTITY_ID' => $requisiteId,
            'NAME' => 'Test bank requisite'
        ]);
        self::assertGreaterThan(
            1,
            $this->linkService->get($bankRequisite->getId())->bankdetail()->ID
        );
    }

    /**
     * @throws BaseException
     * @throws TransportException
     */
    public function testList(): void
    {
        $presetId = $this->presets[0];
        list($companyId, $requisiteId) = $this->addCompanyAndRequisite($presetId);
        $this->createdCompanies[] = $companyId;
        $this->linkService->add([
            'ENTITY_ID' => $requisiteId,
            'NAME' => 'Test bank requisite'
        ]);
        self::assertGreaterThanOrEqual(1, $this->linkService->list([], [], ['ID', 'NAME'])->getBankdetails());
    }

    /**
     * @throws BaseException
     * @throws TransportException
     */
    public function testUpdate(): void
    {
        $presetId = $this->presets[0];
        list($companyId, $requisiteId) = $this->addCompanyAndRequisite($presetId);
        $this->createdCompanies[] = $companyId;
        $bankRequisite = $this->linkService->add([
            'ENTITY_ID' => $requisiteId,
            'NAME' => 'Test bank requisite'
        ]);
        $newName = 'Test2 bank requisite';

        self::assertTrue($this->linkService->update($bankRequisite->getId(), ['NAME' => $newName])->isSuccess());
        self::assertEquals($newName, $this->linkService->get($bankRequisite->getId())->bankdetail()->NAME);
    }

    /**
     * @throws \Bitrix24\SDK\Core\Exceptions\BaseException
     * @throws \Bitrix24\SDK\Core\Exceptions\TransportException
     */
    public function testCountByFilter(): void
    {
        $before = $this->linkService->countByFilter();

        foreach ($this->presets as $presetId) {
            list($companyId, $requisiteId) = $this->addCompanyAndRequisite($presetId);
            $this->createdCompanies[] = $companyId;
            $bankRequisite = $this->linkService->add([
                'ENTITY_ID' => $requisiteId,
                'NAME' => 'Test bank requisite '.$presetId
            ]);
        }
        
        $after = $this->linkService->countByFilter();

        $this->assertEquals($before + count($this->presets), $after);
    }
    
    protected function addCompanyAndRequisite(int $presetId = 0): array {
        $companyId = $this->companyService->add((new CompanyBuilder())->build())->getId();
        $requisiteId = $this->requisiteService->add(
            $companyId,
            self::COMPANY_OWNER_TYPE_ID,
            $presetId,
            'Test requisite '.$presetId,
            (new RequisiteBuilder(self::COMPANY_OWNER_TYPE_ID, $companyId, $presetId))->build()
        )->getId();

        return [$companyId, $requisiteId];
    }
}
