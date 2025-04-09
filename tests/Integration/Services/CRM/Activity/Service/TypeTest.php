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

namespace Bitrix24\SDK\Tests\Integration\Services\CRM\Activity\Service;

use Bitrix24\SDK\Core\Exceptions\BaseException;
use Bitrix24\SDK\Core\Exceptions\TransportException;
use Bitrix24\SDK\Services\CRM\Activity\ActivityContentType;
use Bitrix24\SDK\Services\CRM\Activity\ActivityDirectionType;
use Bitrix24\SDK\Services\CRM\Activity\Result\ActivityItemResult;
use Bitrix24\SDK\Services\CRM\Activity\Service\Activity;
use Bitrix24\SDK\Services\CRM\Activity\ActivityType;
use Bitrix24\SDK\Services\CRM\Contact\Service\Contact;
use Bitrix24\SDK\Services\CRM\Deal\Result\DealItemResult;
use Bitrix24\SDK\Services\CRM\Deal\Result\DealProductRowItemResult;
use Bitrix24\SDK\Tests\Builders\DemoDataGenerator;
use Bitrix24\SDK\Tests\Integration\Fabric;
use PHPUnit\Framework\Attributes\CoversFunction;
use PHPUnit\Framework\Attributes\CoversMethod;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;
use Typhoon\Reflection\TyphoonReflector;
use Bitrix24\SDK\Tests\CustomAssertions\CustomBitrix24Assertions;
use Bitrix24\SDK\Core;

#[CoversClass(Type::class)]
#[CoversMethod(Type::class, 'add')]
#[CoversMethod(Type::class, 'delete')]
#[CoversMethod(Type::class, 'list')]
class TypeTest extends TestCase
{
    use CustomBitrix24Assertions;

    private ActivityType $activityTypeService;

    public function testAllSystemFieldsAnnotated(): void
    {
        $propListFromApi = (new Core\Fields\FieldsFilter())->filterSystemFields(array_keys($this->activityTypeService->fields()->getFieldsDescription()));
        $this->assertBitrix24AllResultItemFieldsAnnotated($propListFromApi, ActivityItemResult::class);
    }

    public function testAllSystemFieldsHasValidTypeAnnotation():void
    {
        $this->assertBitrix24AllResultItemFieldsHasValidTypeAnnotation(
            $this->activityTypeService->fields()->getFieldsDescription(),
            ActivityItemResult::class);
    }

    /**
     * @throws BaseException
     * @throws TransportException
     */
    public function testAdd(): void
    {
        $activityTypeId = $this->activityTypeService->add(
            [
                'TYPE_ID' => 'CALL',
                'NAME' => 'TestActivityType',
                'IS_CONFIGURABLE_TYPE' => 'N'
            ]
        )->getId();
        // successfully add activity type
        $this->assertTrue(true);
    }

    /**
     * @throws BaseException
     * @throws TransportException
     */
    public function testDelete(): void
    {
        $activityTypeId = $this->activityTypeService->add(
            [
                'TYPE_ID' => 'CALL',
                'NAME' => 'TestActivityType',
                'IS_CONFIGURABLE_TYPE' => 'N'
            ]
        )->getId();
        
        $this->assertTrue($this->activityTypeService->delete($activityTypeId)->isSuccess());
    }

    /**
     * @throws BaseException
     * @throws TransportException
     */
    public function testList(): void
    {
        $newActivity = [];
        for ($i = 0; $i < 3; $i++) {
            $newActivityType[$i] = [
                'TYPE_ID' => 'CALL',
                'NAME' => "TestActivityType_$i",
                'IS_CONFIGURABLE_TYPE' => 'N'
            ];

            $this->activityId[] = $this->activityTypeService->add($newActivityType[$i])->getId();;
        }

        $res = $this->activityTypeService->list();

        // Что за getActivities ?
        // Скорее всего, тут должно быть не getActivities
        $isResCountGreatherThenZero = (count($res->getActivities()) > 0) ? true : fasle;

        $this->assertTrue($isResCountGreatherThenZero);
    }
}