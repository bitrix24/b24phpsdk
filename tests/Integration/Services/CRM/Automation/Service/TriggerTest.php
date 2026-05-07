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
use Bitrix24\SDK\Tests\Integration\Factory;
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
    public const TRIGGER_CODE = 'b24phpsdk';
    
    use CustomBitrix24Assertions;
    protected Trigger $triggerService;

    protected function setUp(): void
    {
        $this->triggerService = Factory::getServiceBuilder(true)->getCRMScope()->trigger();
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
        $systemFields = array_filter($allFields, static fn($code): bool => in_array($code, $systemFieldsCodes, true), ARRAY_FILTER_USE_KEY);

        $this->assertBitrix24AllResultItemFieldsHasValidTypeAnnotation(
            $systemFields,
            TriggerItemResult::class);
    }

    /**
     * @throws BaseException
     * @throws TransportException
     */
    public function testAdd(): void
    {
        self::assertEquals(
            1,
            $this->triggerService->add(
                self::TRIGGER_CODE,
                'B24phpsdk trigger')
                ->getCoreResponse()->getResponseData()->getResult()[0]
        );
        $this->triggerService->delete(self::TRIGGER_CODE);
    }

    /**
     * @throws BaseException
     * @throws TransportException
     */
    public function testDelete(): void
    {
        $this->triggerService->add(self::TRIGGER_CODE, 'B24phpsdk trigger');
        self::assertTrue($this->triggerService->delete(self::TRIGGER_CODE)->isSuccess());
    }

    /**
     * @throws BaseException
     * @throws TransportException
     */
    public function testList(): void
    {
        $this->triggerService->add(self::TRIGGER_CODE, 'B24phpsdk trigger');
        self::assertGreaterThanOrEqual(1, $this->triggerService->list()->getTriggers());
        $this->triggerService->delete(self::TRIGGER_CODE);
    }

    /**
     * Get Activity type fields from list response
     */
    protected function getFields(): array
    {
        // add trigger
        $this->triggerService->add(self::TRIGGER_CODE, 'B24phpsdk trigger');
        $list = $this->triggerService->list()->getTriggersArray();
        // delete trigger
        $this->triggerService->delete(self::TRIGGER_CODE);
        //$res = $list[0]->getIterator()->getArrayCopy();
        $res = $list[0];

        return array_keys($res);
    }

    /**
     * Get Activity type fields description from list response
     */
    protected function getFieldsDescription(): array
    {
        // add trigger
        $this->triggerService->add(self::TRIGGER_CODE, 'B24phpsdk trigger');
        $list = $this->triggerService->list()->getTriggersArray();
        // delete trigger
        $this->triggerService->delete(self::TRIGGER_CODE);
        $res = $list[0];

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