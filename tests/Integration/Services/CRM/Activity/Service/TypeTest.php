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
use Bitrix24\SDK\Services\CRM\Activity\Result\ActivitiesResult;
use Bitrix24\SDK\Services\CRM\Activity\Service\Activity;
use Bitrix24\SDK\Services\CRM\Activity\Service\ActivityType;
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
use Faker;

#[CoversClass(ActivityType::class)]
#[CoversMethod(ActivityType::class, 'add')]
#[CoversMethod(ActivityType::class, 'delete')]
#[CoversMethod(ActivityType::class, 'list')]
class TypeTest extends TestCase
{
    use CustomBitrix24Assertions;

    private ActivityType $activityTypeService;
    private Faker\Generator $faker;
    private array $activityTypeIds;

    public function setUp(): void
    {
        $this->activityTypeService = Fabric::getServiceBuilder(true)->getCRMScope()->activityType();
        $this->faker = Faker\Factory::create();
        $this->activityTypeIds = [];
    }

    public function tearDown(): void
    {
        foreach ($this->activityTypeIds as $activityTypeId) {
            $this->activityTypeService->delete($activityTypeId);
        }
    }

    // public function testAllSystemFieldsAnnotated(): void
    // {
    //     $propListFromApi = (new Core\Fields\FieldsFilter())->filterSystemFields(array_keys($this->activityTypeService->fields()->getFieldsDescription()));
    //     $this->assertBitrix24AllResultItemFieldsAnnotated($propListFromApi, ActivityItemResult::class);
    // }

    // public function testAllSystemFieldsHasValidTypeAnnotation():void
    // {
    //     $this->assertBitrix24AllResultItemFieldsHasValidTypeAnnotation(
    //         $this->activityTypeService->fields()->getFieldsDescription(),
    //         ActivityItemResult::class);
    // }

    /**
     * @throws BaseException
     * @throws TransportException
     */
    public function testAdd(): void
    {
        $int = $this->faker->randomNumber(5, true);

        $activityTypeId = $this->activityTypeService->add(
            [
                'TYPE_ID' => 'CALL',
                'NAME' => "TestActivityType_$int",
                'IS_CONFIGURABLE_TYPE' => 'N'
            ]
        )->getId();

        $listOfActivityTypes = $this->activityTypeService->list();

        foreach ($listOfActivityTypes as $item) {
            // successfully add activity type and get list
            $this->assertTrue(!empty($activityTypeId) && $item->NAME == "TestActivityType_$int");
        }
        
    }

    /**
     * @throws BaseException
     * @throws TransportException
     */
    public function testDelete(): void
    {
        $int = $this->faker->randomNumber(5, true);

        $activityTypeId = $this->activityTypeService->add(
            [
                'TYPE_ID' => 'CALL',
                'NAME' => "TestActivityType_$int",
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
        $int = $this->faker->randomNumber(6, true);

        $newActivity = [];
        for ($i = 0; $i < 3; $i++) {
            $newActivityType[$i] = [
                'TYPE_ID' => 'CALL',
                'NAME' => "TestActivityType_$int",
                'IS_CONFIGURABLE_TYPE' => 'N'
            ];

            $this->activityTypeIds[] = $this->activityTypeService->add($newActivityType[$i])->getId();;
        }

        $res = $this->activityTypeService->list();

        $isResCountGreatherThenZero = (count($res->getActivities()) > 0) ? true : fasle;

        $this->assertTrue($isResCountGreatherThenZero);
    }
}