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
use Bitrix24\SDK\Services\CRM\Requisites\Result\RequisitePresetFieldItemResult;
use Bitrix24\SDK\Services\CRM\Requisites\Service\RequisitePresetField;
use Bitrix24\SDK\Services\ServiceBuilder;
use Bitrix24\SDK\Tests\CustomAssertions\CustomBitrix24Assertions;
use Bitrix24\SDK\Tests\Integration\Factory;
use PHPUnit\Framework\Attributes\CoversFunction;
use PHPUnit\Framework\Attributes\CoversMethod;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Uid\Uuid;

/**
 * Class RequisitePresetFieldTest
 *
 * @package Bitrix24\SDK\Tests\Integration\Services\CRM\Requisite\Service
 */
#[CoversMethod(RequisitePresetField::class,'add')]
#[CoversMethod(RequisitePresetField::class,'delete')]
#[CoversMethod(RequisitePresetField::class,'get')]
#[CoversMethod(RequisitePresetField::class,'list')]
#[CoversMethod(RequisitePresetField::class,'fields')]
#[CoversMethod(RequisitePresetField::class,'update')]
#[\PHPUnit\Framework\Attributes\CoversClass(\Bitrix24\SDK\Services\CRM\Requisites\Service\RequisitePresetField::class)]
class RequisitePresetFieldTest extends TestCase
{
    use CustomBitrix24Assertions;

    protected ServiceBuilder $sb;

    protected RequisitePresetField $presetFieldService;

    protected int $presetId;

    protected function setUp(): void
    {
        $this->sb = Factory::getServiceBuilder();
        
        $entityTypeRequisiteId = current(
            array_filter(
                $this->sb->getCRMScope()->enum()->ownerType()->getItems(),
                fn($item): bool => $item->SYMBOL_CODE === 'REQUISITE'
            )
        )->ID;
        
        $countryId = current(
            array_column(
                array_filter(
                    $this->sb->getCRMScope()->requisitePreset()->countries()->getCountries(),
                    fn($item): bool => $item->CODE === 'US'
                ),
                'ID'
            )
        );
        
        $name = sprintf('test req tpl %s', time());
        $this->presetId = $this->sb->getCRMScope()->requisitePreset()->add(
            $entityTypeRequisiteId,
            $countryId,
            $name,
            [
                'XML_ID' => Uuid::v4()->toRfc4122(),
                'ACTIVE' => 'Y',
            ]
        )->getId();
        
        $this->presetFieldService = $this->sb->getCRMScope()->requisitePresetField();
    }

    protected function tearDown(): void
    {
        $this->sb->getCRMScope()->requisitePreset()->delete($this->presetId);
    }

    public function testAllSystemFieldsAnnotated(): void
    {
        $fieldDescriptions = $this->presetFieldService->fields()->getFieldsDescription();
        $propListFromApi = (new Core\Fields\FieldsFilter())->filterSystemFields(array_keys($fieldDescriptions));
        
        $this->assertBitrix24AllResultItemFieldsAnnotated($propListFromApi, RequisitePresetFieldItemResult::class);
    }

    public function testAllSystemFieldsHasValidTypeAnnotation():void
    {
        $allFields = $this->presetFieldService->fields()->getFieldsDescription();
        $systemFieldsCodes = (new Core\Fields\FieldsFilter())->filterSystemFields(array_keys($allFields));
        $systemFields = array_filter($allFields, static fn($code): bool => in_array($code, $systemFieldsCodes, true), ARRAY_FILTER_USE_KEY);

        $this->assertBitrix24AllResultItemFieldsHasValidTypeAnnotation(
            $systemFields,
            RequisitePresetFieldItemResult::class);
    }

    /**
     * @throws BaseException
     * @throws TransportException
     */
    public function testAdd(): void
    {
        $addedItemResult = $this->presetFieldService->add(
            $this->presetId,
            [
                'FIELD_NAME'    => 'RQ_NAME',
                'FIELD_TITLE'   => 'TEST',
                'IN_SHORT_LIST' => 'N',
                'SORT'          => 580
            ]
        );
        self::assertGreaterThanOrEqual(1, $addedItemResult->getId());
    }

    /**
     * @throws BaseException
     * @throws TransportException
     */
    public function testDelete(): void
    {
        $addedItemResult = $this->presetFieldService->add(
            $this->presetId,
            [
                'FIELD_NAME'    => 'RQ_NAME',
                'FIELD_TITLE'   => 'TEST',
                'IN_SHORT_LIST' => 'N',
                'SORT'          => 580
            ]
        );
        self::assertTrue($this->presetFieldService->delete($addedItemResult->getId(), $this->presetId)->isSuccess());
    }

    /**
     * @throws BaseException
     * @throws TransportException
     */
    public function testFields(): void
    {
        self::assertIsArray($this->presetFieldService->fields()->getFieldsDescription());
    }

    /**
     * @throws BaseException
     * @throws TransportException
     */
    public function testGet(): void
    {
        $addedItemResult = $this->presetFieldService->add(
            $this->presetId,
            [
                'FIELD_NAME'    => 'RQ_NAME',
                'FIELD_TITLE'   => 'TEST',
                'IN_SHORT_LIST' => 'N',
                'SORT'          => 580
            ]
        );
        self::assertGreaterThanOrEqual(
            1,
            $this->presetFieldService->get($addedItemResult->getId(), $this->presetId)->presetField()->ID
        );
    }

    /**
     * @throws BaseException
     * @throws TransportException
     */
    public function testList(): void
    {
        $this->presetFieldService->add(
            $this->presetId,
            [
                'FIELD_NAME'    => 'RQ_NAME',
                'FIELD_TITLE'   => 'TEST',
                'IN_SHORT_LIST' => 'N',
                'SORT'          => 580
            ]
        );
        self::assertGreaterThanOrEqual(1, $this->presetFieldService->list($this->presetId)->getPresetFields());
    }

    /**
     * @throws BaseException
     * @throws TransportException
     */
    public function testUpdate(): void
    {
        $addedItemResult = $this->presetFieldService->add(
            $this->presetId,
            [
                'FIELD_NAME'    => 'RQ_NAME',
                'FIELD_TITLE'   => 'TEST',
                'IN_SHORT_LIST' => 'N',
                'SORT'          => 580
            ]
        );
        $newTitle = 'Test2';

        self::assertTrue(
            $this->presetFieldService->update(
                $addedItemResult->getId(),
                $this->presetId,
                [
                    'FIELD_NAME'    => 'RQ_NAME',
                    'FIELD_TITLE' => $newTitle
                ]
            )->isSuccess()
        );
        self::assertEquals($newTitle, $this->presetFieldService->get($addedItemResult->getId(), $this->presetId)->presetField()->FIELD_TITLE);
    }

}
