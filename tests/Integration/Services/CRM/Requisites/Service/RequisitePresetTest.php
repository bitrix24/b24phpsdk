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
use Bitrix24\SDK\Services\CRM\Requisites\Result\RequisitePresetItemResult;
use Bitrix24\SDK\Services\CRM\Lead\Result\LeadItemResult;
use Bitrix24\SDK\Services\CRM\Lead\Service\Lead;
use Bitrix24\SDK\Services\CRM\Requisites\Service\Requisite;
use Bitrix24\SDK\Services\CRM\Requisites\Service\RequisitePreset;
use Bitrix24\SDK\Services\ServiceBuilder;
use Bitrix24\SDK\Tests\Builders\Services\CRM\CompanyBuilder;
use Bitrix24\SDK\Tests\Builders\Services\CRM\RequisiteBuilder;
use Bitrix24\SDK\Tests\CustomAssertions\CustomBitrix24Assertions;
use Bitrix24\SDK\Tests\Integration\Fabric;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\CoversFunction;
use PHPUnit\Framework\Attributes\CoversMethod;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Uid\Uuid;

#[CoversMethod(RequisitePreset::class, 'fields')]
#[CoversMethod(RequisitePreset::class, 'countries')]
#[CoversMethod(RequisitePreset::class, 'add')]
#[CoversMethod(RequisitePreset::class, 'delete')]
#[CoversMethod(RequisitePreset::class, 'get')]
#[CoversMethod(RequisitePreset::class, 'list')]
#[CoversMethod(RequisitePreset::class, 'update')]
class RequisitePresetTest extends TestCase
{
    use CustomBitrix24Assertions;

    protected ServiceBuilder $sb;

    private array $createdCompanies = [];
    private int $entityTypeRequisiteId;
    private int $countryId;

    protected function setUp(): void
    {
        $this->sb = Fabric::getServiceBuilder();
        $this->entityTypeRequisiteId = current(
            array_filter(
                $this->sb->getCRMScope()->enum()->ownerType()->getItems(),
                fn($item): bool => $item->SYMBOL_CODE === 'REQUISITE'
            )
        )->ID;
        $this->countryId = current(
            array_column(
                array_filter(
                    $this->sb->getCRMScope()->requisitePreset()->countries()->getCountries(),
                    fn($item): bool => $item->CODE === 'US'
                ),
                'ID'
            )
        );
    }

    protected function tearDown(): void
    {
    }

    public function testFields(): void
    {
        self::assertIsArray($this->sb->getCRMScope()->requisitePreset()->fields()->getFieldsDescription());
    }

    public function testAllSystemFieldsAnnotated(): void
    {
        $propListFromApi = (new Core\Fields\FieldsFilter())->filterSystemFields(
            array_keys($this->sb->getCRMScope()->requisitePreset()->fields()->getFieldsDescription())
        );
        $this->assertBitrix24AllResultItemFieldsAnnotated($propListFromApi, RequisitePresetItemResult::class);
    }

    public function testAllSystemFieldsHasValidTypeAnnotation(): void
    {
        $allFields = $this->sb->getCRMScope()->requisitePreset()->fields()->getFieldsDescription();
        $systemFieldsCodes = (new Core\Fields\FieldsFilter())->filterSystemFields(array_keys($allFields));
        $systemFields = array_filter($allFields, static fn($code): bool => in_array($code, $systemFieldsCodes, true), ARRAY_FILTER_USE_KEY);

        $this->assertBitrix24AllResultItemFieldsHasValidTypeAnnotation($systemFields, RequisitePresetItemResult::class);
    }

    public function testCountries(): void
    {
        $this->assertGreaterThan(1, $this->sb->getCRMScope()->requisitePreset()->countries()->getCountries());
    }

    public function testList(): void
    {
        $items = $this->sb->getCRMScope()->requisitePreset()->list([], [], [])->getRequisitePresets();
        $this->assertGreaterThan(1, count($items));
    }

    public function testAdd(): void
    {
        $name = sprintf('test req tpl %s', time());
        $tplId = $this->sb->getCRMScope()->requisitePreset()->add(
            $this->entityTypeRequisiteId,
            $this->countryId,
            $name,
            [
                'XML_ID' => Uuid::v4()->toRfc4122(),
                'ACTIVE' => 'Y',
            ]
        )->getId();
        $requisitePresetItemResult = $this->sb->getCRMScope()->requisitePreset()->get($tplId)->requisitePreset();
        $this->assertEquals($name, $requisitePresetItemResult->NAME);
        $this->assertTrue($this->sb->getCRMScope()->requisitePreset()->delete($tplId)->isSuccess());
    }

    public function testDelete(): void
    {
        $tplId = $this->sb->getCRMScope()->requisitePreset()->add(
            $this->entityTypeRequisiteId,
            $this->countryId,
            sprintf('test tpl %s', Uuid::v4()->toRfc4122()),
            [
                'XML_ID' => Uuid::v4()->toRfc4122(),
                'ACTIVE' => 'Y',
            ]
        )->getId();

        $this->assertTrue($this->sb->getCRMScope()->requisitePreset()->delete($tplId)->isSuccess());

        $this->expectException(ItemNotFoundException::class);
        $this->sb->getCRMScope()->requisitePreset()->get($tplId)->requisitePreset();
    }

    public function testUpdate(): void
    {
        $name = sprintf('test req tpl %s', time());
        $tplId = $this->sb->getCRMScope()->requisitePreset()->add(
            $this->entityTypeRequisiteId,
            $this->countryId,
            $name,
            [
                'XML_ID' => Uuid::v4()->toRfc4122(),
                'ACTIVE' => 'Y',
            ]
        )->getId();
        $addedItem = $this->sb->getCRMScope()->requisitePreset()->get($tplId)->requisitePreset();
        $this->assertEquals($name, $addedItem->NAME);

        $name = 'new name';
        $this->assertTrue($this->sb->getCRMScope()->requisitePreset()->update($tplId, ['NAME' => $name])->isSuccess());

        $addedItem = $this->sb->getCRMScope()->requisitePreset()->get($tplId)->requisitePreset();
        $this->assertEquals($name, $addedItem->NAME);
        $this->assertTrue($this->sb->getCRMScope()->requisitePreset()->delete($tplId)->isSuccess());
    }
}