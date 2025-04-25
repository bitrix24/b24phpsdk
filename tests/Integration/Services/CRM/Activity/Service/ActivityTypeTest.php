<?php

/**
 * This file is part of the bitrix24-php-sdk package.
 *
 * © Gleb Starikov <gleb.starikov1998@mail.ru>
 *
 * For the full copyright and license information, please view the MIT-LICENSE.txt
 * file that was distributed with this source code.
 */

declare(strict_types=1);

namespace Bitrix24\SDK\Tests\Integration\Services\CRM\Activity\Service;

use Bitrix24\SDK\Core\Exceptions\BaseException;
use Bitrix24\SDK\Core\Exceptions\TransportException;
use Bitrix24\SDK\Services\CRM\Activity\Service\ActivityType;
use Bitrix24\SDK\Services\CRM\Activity\Result\ActivityTypeResult;
use Bitrix24\SDK\Tests\Integration\Fabric;
use PHPUnit\Framework\Attributes\CoversMethod;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;
use Bitrix24\SDK\Tests\CustomAssertions\CustomBitrix24Assertions;
use Bitrix24\SDK\Tests\CustomAssertions\AnnotationsParser;
use Bitrix24\SDK\Tests\Integration\Services\CRM\Activity\PhantomMethods\ActivityTypePhantomMethods;


#[CoversClass(ActivityType::class)]
#[CoversMethod(ActivityType::class, 'add')]
#[CoversMethod(ActivityType::class, 'delete')]
#[CoversMethod(ActivityType::class, 'list')]
class ActivityTypeTest extends TestCase
{
    use CustomBitrix24Assertions;

    private ActivityType $activityTypeService;
    
    private array $activityTypeIds;

    protected function setUp(): void
    {
        $this->activityTypeService = Fabric::getServiceBuilder(true)->getCRMScope()->activityType();
        $this->activityTypeIds = [];
    }

    protected function tearDown(): void
    {
        foreach ($this->activityTypeIds as $activityTypeId) {
            $this->activityTypeService->delete($activityTypeId);
        }
    }

    public function testAllSystemFieldsAnnotated(): void
    {
        
        $b24fields = (new ActivityTypePhantomMethods())->getFields();
        $annotationFields = (new AnnotationsParser())->parse(ActivityTypeResult::class);

        $this->assertEqualsCanonicalizing(
            $b24fields,
            $annotationFields,
            sprintf(
                'in phpdocs annotations for class «%s» we not found fields from actual api response: %s',
                ActivityTypeResult::class,
                implode(', ', array_values(array_diff($b24fields, $annotationFields)))
            )
        );
    }

    public function testAllSystemFieldsHasValidTypeAnnotation():void
    {
        $this->assertBitrix24AllResultItemFieldsHasValidTypeAnnotation(
            (new ActivityTypePhantomMethods())->getFieldsDescription(),
            ActivityTypeResult::class
        );
    }

    /**
     * @throws BaseException
     * @throws TransportException
     */
    public function testAdd(): void
    {
        $int = time();

        $this->activityTypeService->add(
            [
                'TYPE_ID' => 'TYPENAME_' . $int,
                'NAME' => 'NAME_' . $int,
            ]
        )->getId();

        $activityTypesResult = $this->activityTypeService->list();
        $res = $activityTypesResult->getActivityTypes();


        $this->assertNotEmpty($res, 'List of activity types should not be empty.');

        // successfully add activity type and get lis
        $found = false;

        foreach ($res as $re) {
            if ($re->NAME == 'NAME_' . $int) {
                $found = true;
                break;
            }
        }

        $this->assertTrue($found, 'Expected activity type not found in the list.');
    }

    /**
     * @throws BaseException
     * @throws TransportException
     */
    public function testDelete(): void
    {
        $int = time();
        $int += 1;

        $typeId = 'TYPENAME_' . $int;

        $this->activityTypeService->add(
            [
                'TYPE_ID' => $typeId,
                'NAME' => 'NAME_' . $int,
            ]
        )->getId();
        
        $this->assertTrue($this->activityTypeService->delete($typeId)->isSuccess());
    }

    /**
     * @throws BaseException
     * @throws TransportException
     */
    public function testList(): void
    {
        for ($i = 0; $i < 3; $i++) {

            $int = time();
            $int = $int + 2 + $i;

            $newActivityType[$i] = [
                'TYPE_ID' => 'TEST_' . $int,
                'NAME' => 'NAME_' . $int,
            ];

            $this->activityTypeService->add($newActivityType[$i])->getId();
            $this->activityTypeIds[] = $newActivityType[$i]['TYPE_ID'];
        }

        $activityTypesResult = $this->activityTypeService->list();

        $isResCountGreatherThenZero = $activityTypesResult->getActivityTypes() !== [];

        $this->assertTrue($isResCountGreatherThenZero);
    }
}
