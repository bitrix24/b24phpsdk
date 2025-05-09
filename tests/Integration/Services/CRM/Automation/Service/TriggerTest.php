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

namespace Bitrix24\SDK\Tests\Integration\Services\CRM\Automation\Service;

use Bitrix24\SDK\Core\Exceptions\BaseException;
use Bitrix24\SDK\Core\Exceptions\TransportException;
use Bitrix24\SDK\Core;
use Bitrix24\SDK\Services\CRM\Contact\Result\ContactItemResult;
use Bitrix24\SDK\Services\CRM\Automation\Result\TriggerItemResult;
use Bitrix24\SDK\Services\CRM\Automation\Service\Trigger;
use Bitrix24\SDK\Tests\CustomAssertions\CustomBitrix24Assertions;
use Bitrix24\SDK\Tests\Integration\Fabric;
use PHPUnit\Framework\Attributes\CoversFunction;
use PHPUnit\Framework\Attributes\CoversMethod;
use PHPUnit\Framework\TestCase;

/**
 * Class LeadTest
 *
 * @package Bitrix24\SDK\Tests\Integration\Services\CRM\Automation\Service
 */
#[CoversMethod(Trigger::class,'add')]
#[CoversMethod(Trigger::class,'delete')]
#[CoversMethod(Trigger::class,'list')]
#[CoversMethod(Trigger::class,'execute')]
class TriggerTest extends TestCase
{
    const TRIGGER_CODE = 'b24phpsdk';
    
    use CustomBitrix24Assertions;
    protected Trigger $triggerService;

    public function setUp(): void
    {
        $this->triggerService = Fabric::getServiceBuilder()->getCRMScope()->trigger();
    }
    
    public function testAllSystemFieldsAnnotated(): void
    {
        $fields = $this->getFields();
        $propListFromApi = (new Core\Fields\FieldsFilter())->filterSystemFields($fields);
        $this->assertBitrix24AllResultItemFieldsAnnotated($propListFromApi, TriggerItemResult::class);
    }

    public function testAllSystemFieldsHasValidTypeAnnotation():void
    {
        $allFields = $this->getFieldsDescription();
        $systemFieldsCodes = (new Core\Fields\FieldsFilter())->filterSystemFields(array_keys($allFields));
        $systemFields = array_filter($allFields, static function ($code) use ($systemFieldsCodes) {
            return in_array($code, $systemFieldsCodes, true);
        }, ARRAY_FILTER_USE_KEY);

        $this->assertBitrix24AllResultItemFieldsHasValidTypeAnnotation(
            $systemFields,
            TriggerItemResult::class);
    }

    /**
     * @throws BaseException
     * @throws TransportException
     * @covers Trigger::add
     */
    public function testAdd(): void
    {
        self::assertGreaterThan(
            1,
            $this->triggerService->add(['CODE' => self::TRIGGER_CODE, 'NAME' => 'B24phpsdk trigger'])->getId()
        );
        $this->triggerService->delete(['CODE' => self::TRIGGER_CODE]);
    }

    /**
     * @throws BaseException
     * @throws TransportException
     * @covers Trigger::delete
     */
    public function testDelete(): void
    {
        $this->triggerService->add(['CODE' => self::TRIGGER_CODE, 'NAME' => 'B24phpsdk trigger']);
        self::assertTrue($this->triggerService->delete(['CODE' => self::TRIGGER_CODE])->getId())->isSuccess());
    }

    /**
     * @covers Lead::fields
     * @throws BaseException
     * @throws TransportException
     */
    public function testFields(): void
    {
        self::assertIsArray($this->triggerService->fields()->getFieldsDescription());
    }

    /**
     * @throws BaseException
     * @throws TransportException
     * @covers Trigger::list
     */
    public function testList(): void
    {
        $this->triggerService->add(['CODE' => self::TRIGGER_CODE, 'NAME' => 'B24phpsdk trigger']);
        self::assertGreaterThanOrEqual(1, $this->triggerService->list()->getTriggers());
    }

    

    /**
     * @throws \Bitrix24\SDK\Core\Exceptions\BaseException
     * @throws \Bitrix24\SDK\Core\Exceptions\TransportException
     * @covers \Bitrix24\SDK\Services\CRM\Deal\Service\Deal::countByFilter
     */
    public function testCountByFilter(): void
    {
        $before = $this->triggerService->countByFilter();

        $newItemsCount = 60;
        $items = [];
        for ($i = 1; $i <= $newItemsCount; $i++) {
            $items[] = ['TITLE' => 'TITLE-' . $i];
        }
        $cnt = 0;
        foreach ($this->triggerService->batch->add($items) as $item) {
            $cnt++;
        }
        self::assertEquals(count($items), $cnt);

        $after = $this->triggerService->countByFilter();

        $this->assertEquals($before + $newItemsCount, $after);
    }

    /**
     * Get Activity type fields from list response
     */
    protected function getFields(): array
    {
        // add trigger
        $this->triggerService->add(['CODE' => self::TRIGGER_CODE, 'NAME' => 'B24phpsdk trigger']);
        $list = $this->triggerService->list()->getTriggers();
        // delete trigger
        $this->triggerService->delete(['CODE' => self::TRIGGER_CODE]);
        $res = $list[0]->getIterator()->getArrayCopy();
        
        $fields = [];
        foreach (array_keys($res) as $key) {
            $fields[] = $key;
        }

        return $fields;
    }

    /**
     * Get Activity type fields description from list response
     */
    protected function getFieldsDescription(): array
    {
        // add trigger
        $this->triggerService->add(['CODE' => self::TRIGGER_CODE, 'NAME' => 'B24phpsdk trigger']);
        $list = $this->triggerService->list()->getTriggers();
        // delete trigger
        $this->triggerService->delete(['CODE' => self::TRIGGER_CODE]);
        $res = $list[0]->getIterator()->getArrayCopy();

        $fields = [];
        foreach ($res as $key => $value) {
            $type = '';

            if (is_string($value)) {
                $type = 'string';
            } elseif (is_int($value)) {
                $type = 'int';
            } elseif (is_bool($value)) {
                $type = 'bool';
            } elseif (is_array($value)) {
                $type = 'array';
            }

            $fields[$key] = ['type' => $type];
        }

        return $fields;
    }
}