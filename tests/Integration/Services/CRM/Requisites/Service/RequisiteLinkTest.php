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
use Bitrix24\SDK\Services\CRM\Deal\Service\Deal;
use Bitrix24\SDK\Services\CRM\Requisites\Service\RequisiteBankdetail;

use Bitrix24\SDK\Tests\CustomAssertions\CustomBitrix24Assertions;
use Bitrix24\SDK\Tests\Integration\Factory;
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

    public const COMPANY_OWNER_TYPE_ID = 4;

    public const DEAL_OWNER_TYPE_ID = 2;

    protected ServiceBuilder $sb;

    protected RequisiteLink $linkService;

    protected Company $companyService;

    protected Requisite $requisiteService;

    protected Deal $dealService;

    private int $companyId = 0;

    private int $requisiteId = 0;

    private int $dealId = 0;

    #[\Override]
    protected function setUp(): void
    {
        $this->sb = Factory::getServiceBuilder();
        $this->companyService = $this->sb->getCRMScope()->company();
        $this->requisiteService = $this->sb->getCRMScope()->requisite();
        $this->linkService = $this->sb->getCRMScope()->requisiteLink();
        $presetId = 0;
        $requisitePreset = $this->sb->getCRMScope()->requisitePreset();
        foreach ($requisitePreset->list()->getRequisitePresets() as $requisitePresetItemResult) {
            $presetId = $requisitePresetItemResult->ID;
            break;
        }

        [$this->companyId, $this->requisiteId] = $this->addCompanyAndRequisite($presetId);
        $this->dealService = $this->sb->getCRMScope()->deal();
        $this->dealId = $this->dealService->add(
            [
                'TITLE' => 'test requisite link 2',
                'COMPANY_ID' => strval($this->companyId),
            ]
        )->getId();
    }

    #[\Override]
    protected function tearDown(): void
    {
        $this->companyService->delete($this->companyId);
        $this->dealService->delete($this->dealId);
    }

    public function testAllSystemFieldsAnnotated(): void
    {
        $fieldDescriptions = $this->linkService->fields()->getFieldsDescription();
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
    public function testRegister(): void
    {
        $updatedItemResult = $this->linkService->register([
            'ENTITY_TYPE_ID' => self::DEAL_OWNER_TYPE_ID,
            'ENTITY_ID' => $this->dealId,
            'REQUISITE_ID' => $this->requisiteId,
            'BANK_DETAIL_ID' => 0,
            'MC_REQUISITE_ID' => 0,
            'MC_BANK_DETAIL_ID' => 0,
        ]);
        self::assertTrue($updatedItemResult->isSuccess());
    }

    /**
     * @throws BaseException
     * @throws TransportException
     */
    public function testUnregister(): void
    {
        $this->linkService->register([
            'ENTITY_TYPE_ID' => self::DEAL_OWNER_TYPE_ID,
            'ENTITY_ID' => $this->dealId,
            'REQUISITE_ID' => $this->requisiteId,
            'BANK_DETAIL_ID' => 0,
            'MC_REQUISITE_ID' => 0,
            'MC_BANK_DETAIL_ID' => 0,
        ]);
        self::assertTrue($this->linkService->unregister(self::DEAL_OWNER_TYPE_ID, $this->dealId)->isSuccess());
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
        $this->linkService->register([
            'ENTITY_TYPE_ID' => self::DEAL_OWNER_TYPE_ID,
            'ENTITY_ID' => $this->dealId,
            'REQUISITE_ID' => $this->requisiteId,
            'BANK_DETAIL_ID' => 0,
            'MC_REQUISITE_ID' => 0,
            'MC_BANK_DETAIL_ID' => 0,
        ]);
        self::assertGreaterThan(
            1,
            $this->linkService->get(self::DEAL_OWNER_TYPE_ID, $this->dealId)->link()->ENTITY_ID
        );
    }

    /**
     * @throws BaseException
     * @throws TransportException
     */
    public function testList(): void
    {
        $this->linkService->register([
            'ENTITY_TYPE_ID' => self::DEAL_OWNER_TYPE_ID,
            'ENTITY_ID' => $this->dealId,
            'REQUISITE_ID' => $this->requisiteId,
            'BANK_DETAIL_ID' => 0,
            'MC_REQUISITE_ID' => 0,
            'MC_BANK_DETAIL_ID' => 0,
        ]);
        self::assertGreaterThanOrEqual(1, $this->linkService->list([], [], ['REQUISITE_ID', 'BANK_DETAIL_ID'])->getLinks());
    }

    /**
     * @throws \Bitrix24\SDK\Core\Exceptions\BaseException
     * @throws \Bitrix24\SDK\Core\Exceptions\TransportException
     */
    public function testCountByFilter(): void
    {
        $before = $this->linkService->countByFilter();

        $dealId = $this->dealService->add(
            [
                'TITLE' => 'test requisite link deal',
                'COMPANY_ID' => strval($this->companyId),
            ]
        )->getId();
        $updatedItemResult = $this->linkService->register([
            'ENTITY_TYPE_ID' => self::DEAL_OWNER_TYPE_ID,
            'ENTITY_ID' => $dealId,
            'REQUISITE_ID' => $this->requisiteId,
            'BANK_DETAIL_ID' => 0,
            'MC_REQUISITE_ID' => 0,
            'MC_BANK_DETAIL_ID' => 0,
        ]);
        
        self::assertTrue($updatedItemResult->isSuccess());
        
        $after = $this->linkService->countByFilter();

        $this->assertEquals($before + 1, $after);
        
        $this->dealService->delete($dealId);
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
