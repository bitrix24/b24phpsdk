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

namespace Bitrix24\SDK\Tests\Integration\Services\CRM\Requisites\Service;

use Bitrix24\SDK\Core\Exceptions\BaseException;
use Bitrix24\SDK\Core\Exceptions\ItemNotFoundException;
use Bitrix24\SDK\Core\Exceptions\TransportException;
use Bitrix24\SDK\Core;
use Bitrix24\SDK\Services\CRM\Requisites\Result\RequisiteItemResult;
use Bitrix24\SDK\Services\CRM\Lead\Result\LeadItemResult;
use Bitrix24\SDK\Services\CRM\Lead\Service\Lead;
use Bitrix24\SDK\Services\CRM\Requisites\Service\Requisite;
use Bitrix24\SDK\Services\ServiceBuilder;
use Bitrix24\SDK\Tests\Builders\Services\CRM\CompanyBuilder;
use Bitrix24\SDK\Tests\Builders\Services\CRM\RequisiteBuilder;
use Bitrix24\SDK\Tests\CustomAssertions\CustomBitrix24Assertions;
use Bitrix24\SDK\Tests\Integration\Fabric;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\CoversFunction;
use PHPUnit\Framework\Attributes\CoversMethod;
use PHPUnit\Framework\TestCase;

#[CoversClass(Requisite::class, 'fields')]
#[CoversMethod(Requisite::class, 'add')]
#[CoversMethod(Requisite::class, 'delete')]
#[CoversMethod(Requisite::class, 'get')]
#[CoversMethod(Requisite::class, 'list')]
#[CoversMethod(Requisite::class, 'update')]
class RequisiteTest extends TestCase
{
    use CustomBitrix24Assertions;

    protected ServiceBuilder $sb;
    private array $createdCompanies = [];
    private array $createdRequisites = [];
    private int $requisitePresetId;
    private int $entityTypeIdCompany;

    public function setUp(): void
    {
        $this->sb = Fabric::getServiceBuilder();
        $this->requisitePresetId = current(
            array_filter(
                $this->sb->getCRMScope()->requisitePreset()->list()->getRequisitePresets(),
                function ($item) {
                    return str_contains($item->XML_ID, 'COMPANY#');
                }
            )
        )->ID;
        $this->entityTypeIdCompany = current(
            array_filter(
                $this->sb->getCRMScope()->enum()->ownerType()->getItems(),
                function ($item) {
                    return $item->SYMBOL_CODE === 'COMPANY';
                }
            )
        )->ID;
    }

    public function tearDown(): void
    {
        foreach ($this->sb->getCRMScope()->requisite()->batch->delete($this->createdRequisites) as $result) {
        }
        foreach ($this->sb->getCRMScope()->company()->batch->delete($this->createdCompanies) as $result) {
        }
    }

    public function testFields(): void
    {
        self::assertIsArray($this->sb->getCRMScope()->requisite()->fields()->getFieldsDescription());
    }

    public function testAllSystemFieldsAnnotated(): void
    {
        $propListFromApi = (new Core\Fields\FieldsFilter())->filterSystemFields(
            array_keys($this->sb->getCRMScope()->requisite()->fields()->getFieldsDescription())
        );
        $this->assertBitrix24AllResultItemFieldsAnnotated($propListFromApi, RequisiteItemResult::class);
    }

    public function testAllSystemFieldsHasValidTypeAnnotation(): void
    {
        $allFields = $this->sb->getCRMScope()->requisite()->fields()->getFieldsDescription();
        $systemFieldsCodes = (new Core\Fields\FieldsFilter())->filterSystemFields(array_keys($allFields));
        $systemFields = array_filter($allFields, static function ($code) use ($systemFieldsCodes) {
            return in_array($code, $systemFieldsCodes, true);
        }, ARRAY_FILTER_USE_KEY);

        $this->assertBitrix24AllResultItemFieldsHasValidTypeAnnotation($systemFields, RequisiteItemResult::class);
    }

    public function testAdd(): void
    {
        $companyId = $this->sb->getCRMScope()->company()->add((new CompanyBuilder())->build())->getId();
        $this->createdCompanies[] = $companyId;

        $reqName = sprintf('test req %s', time());

        $reqId = $this->sb->getCRMScope()->requisite()->add(
            $companyId,
            $this->entityTypeIdCompany,
            $this->requisitePresetId,
            $reqName,
            (new RequisiteBuilder($this->entityTypeIdCompany, $companyId, $this->requisitePresetId))->build()
        )->getId();
        $this->createdRequisites[] = $reqId;

        $addedReq = $this->sb->getCRMScope()->requisite()->get($reqId)->requisite();

        $this->assertEquals($reqName, $addedReq->NAME);
        $this->assertEquals($this->entityTypeIdCompany, $addedReq->ENTITY_TYPE_ID);
        $this->assertEquals($this->requisitePresetId, $addedReq->PRESET_ID);
    }

    public function testDelete(): void
    {
        $companyId = $this->sb->getCRMScope()->company()->add((new CompanyBuilder())->build())->getId();
        $this->createdCompanies[] = $companyId;

        $reqName = sprintf('test req %s', time());

        $reqId = $this->sb->getCRMScope()->requisite()->add(
            $companyId,
            $this->entityTypeIdCompany,
            $this->requisitePresetId,
            $reqName,
            (new RequisiteBuilder($this->entityTypeIdCompany, $companyId, $this->requisitePresetId))->build()
        )->getId();

        $this->assertTrue($this->sb->getCRMScope()->requisite()->delete($reqId)->isSuccess());

        $this->expectException(ItemNotFoundException::class);
        $addedReq = $this->sb->getCRMScope()->requisite()->get($reqId)->requisite();
    }

    public function testList(): void
    {
        $companyId = $this->sb->getCRMScope()->company()->add((new CompanyBuilder())->build())->getId();
        $this->createdCompanies[] = $companyId;

        $reqName = sprintf('test req %s', time());

        $reqId = $this->sb->getCRMScope()->requisite()->add(
            $companyId,
            $this->entityTypeIdCompany,
            $this->requisitePresetId,
            $reqName,
            (new RequisiteBuilder($this->entityTypeIdCompany, $companyId, $this->requisitePresetId))->build()
        )->getId();
        $this->createdRequisites[] = $reqId;

        $this->assertContains(
            $reqId,
            array_column(
                $this->sb->getCRMScope()->requisite()->list([], ['ID' => $reqId], [])->getRequisites(),
                'ID'
            )
        );
    }

    public function testUpdate(): void
    {
        $companyId = $this->sb->getCRMScope()->company()->add((new CompanyBuilder())->build())->getId();
        $this->createdCompanies[] = $companyId;

        $reqName = sprintf('test req %s', time());

        $reqId = $this->sb->getCRMScope()->requisite()->add(
            $companyId,
            $this->entityTypeIdCompany,
            $this->requisitePresetId,
            $reqName,
            (new RequisiteBuilder($this->entityTypeIdCompany, $companyId, $this->requisitePresetId))->build()
        )->getId();
        $this->createdRequisites[] = $reqId;

        $addedReq = $this->sb->getCRMScope()->requisite()->get($reqId)->requisite();

        $this->assertEquals($reqName, $addedReq->NAME);
        $this->assertEquals($this->entityTypeIdCompany, $addedReq->ENTITY_TYPE_ID);
        $this->assertEquals($this->requisitePresetId, $addedReq->PRESET_ID);

        $newName = 'new name';
        $this->assertTrue($this->sb->getCRMScope()->requisite()->update($reqId, ['NAME' => $newName])->isSuccess());

        $updatedReq = $this->sb->getCRMScope()->requisite()->get($reqId)->requisite();
        $this->assertEquals($newName, $updatedReq->NAME);
    }
}