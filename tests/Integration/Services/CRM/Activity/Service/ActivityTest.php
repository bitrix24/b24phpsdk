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
use Bitrix24\SDK\Services\CRM\Activity\Result\ActivityItemResult;
use Bitrix24\SDK\Services\CRM\Activity\Service\Activity;
use Bitrix24\SDK\Services\CRM\Activity\ActivityType;
use Bitrix24\SDK\Services\CRM\Contact\Service\Contact;
use Bitrix24\SDK\Tests\Builders\DemoDataGenerator;
use Bitrix24\SDK\Tests\Integration\Fabric;
use PHPUnit\Framework\Attributes\CoversMethod;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;
use Bitrix24\SDK\Tests\CustomAssertions\CustomBitrix24Assertions;
use Bitrix24\SDK\Core;

#[CoversClass(Activity::class)]
#[CoversMethod(Activity::class, 'add')]
#[CoversMethod(Activity::class, 'delete')]
#[CoversMethod(Activity::class, 'fields')]
#[CoversMethod(Activity::class, 'get')]
#[CoversMethod(Activity::class, 'list')]
#[CoversMethod(Activity::class, 'update')]
#[CoversMethod(Activity::class, 'countByFilter')]
class ActivityTest extends TestCase
{
    use CustomBitrix24Assertions;

    private Activity $activityService;

    private Contact $contactService;

    private array $contactId;

    private array $activityId;

    public function testAllSystemFieldsAnnotated(): void
    {
        $propListFromApi = (new Core\Fields\FieldsFilter())->filterSystemFields(
            array_keys($this->activityService->fields()->getFieldsDescription())
        );
        $this->assertBitrix24AllResultItemFieldsAnnotated($propListFromApi, ActivityItemResult::class);
    }

    public function testAllSystemFieldsHasValidTypeAnnotation():void
    {
        $this->assertBitrix24AllResultItemFieldsHasValidTypeAnnotation(
            $this->activityService->fields()->getFieldsDescription(),
            ActivityItemResult::class);
    }

    /**
     * @throws BaseException
     * @throws TransportException
     */
    public function testGet(): void
    {
        $contactId = $this->contactService->add(['NAME' => 'test contact'])->getId();
        $this->contactId[] = $contactId;

        $newActivity = [
            'OWNER_ID' => $contactId,
            'OWNER_TYPE_ID' => 3,
            'TYPE_ID' => ActivityType::call->value,
            'PROVIDER_ID' => 'VOXIMPLANT_CALL',
            'PROVIDER_TYPE_ID' => 'CALL',
            'SUBJECT' => 'test activity',
            'DESCRIPTION' => 'test activity description',
            'DESCRIPTION_TYPE' => '1',
            'DIRECTION' => '2',
            'COMMUNICATIONS' => [
                0 => [
                    'TYPE' => 'PHONE',
                    'VALUE' => DemoDataGenerator::getMobilePhone()->getNationalNumber(),
                ],
            ],
            'RESULT_SUM' => 500,
            'RESULT_CURRENCY_ID' => 'USD'
        ];
        $activityId = $this->activityService->add($newActivity)->getId();
        $this->activityId[] = $activityId;

        $activity = $this->activityService->get($activityId)->activity();

        $this->assertEquals($newActivity['OWNER_ID'], $activity->OWNER_ID);
        $this->assertEquals($newActivity['SUBJECT'], $activity->SUBJECT);
    }

    /**
     * @throws BaseException
     * @throws TransportException
     */
    public function testAdd(): void
    {
        $contactId = $this->contactService->add(['NAME' => 'test contact'])->getId();
        $this->contactId[] = $contactId;
        $this->activityId[] = $this->activityService->add(
            [
                'OWNER_ID' => $contactId,
                'OWNER_TYPE_ID' => 3,
                'TYPE_ID' => ActivityType::call->value,
                'PROVIDER_ID' => 'VOXIMPLANT_CALL',
                'PROVIDER_TYPE_ID' => 'CALL',
                'SUBJECT' => 'test activity',
                'DESCRIPTION' => 'test activity description',
                'DESCRIPTION_TYPE' => '1',
                'DIRECTION' => '2',
                'COMMUNICATIONS' => [
                    0 => [
                        'TYPE' => 'PHONE',
                        'VALUE' => DemoDataGenerator::getMobilePhone()->getNationalNumber(),
                    ],
                ],
            ]
        )->getId();
        // successfully add activity
        $this->assertTrue(true);
    }

    /**
     * @throws BaseException
     * @throws TransportException
     */
    public function testDelete(): void
    {
        $contactId = $this->contactService->add(['NAME' => 'test contact'])->getId();
        $this->contactId[] = $contactId;
        $activityId = $this->activityService->add(
            [
                'OWNER_ID' => $contactId,
                'OWNER_TYPE_ID' => 3,
                'TYPE_ID' => ActivityType::call->value,
                'PROVIDER_ID' => 'VOXIMPLANT_CALL',
                'PROVIDER_TYPE_ID' => 'CALL',
                'SUBJECT' => 'test activity',
                'DESCRIPTION' => 'test activity description',
                'DESCRIPTION_TYPE' => '1',
                'DIRECTION' => '2',
                'COMMUNICATIONS' => [
                    0 => [
                        'TYPE' => 'PHONE',
                        'VALUE' => DemoDataGenerator::getMobilePhone()->getNationalNumber(),
                    ],
                ],
            ]
        )->getId();
        $this->assertTrue($this->activityService->delete($activityId)->isSuccess());
    }

    /**
     * @throws BaseException
     * @throws TransportException
     */
    public function testFields(): void
    {
        self::assertIsArray($this->activityService->fields()->getFieldsDescription());
    }

    /**
     * @throws BaseException
     * @throws TransportException
     */
    public function testList(): void
    {
        $contactId = $this->contactService->add(['NAME' => 'test contact'])->getId();
        $this->contactId[] = $contactId;

        $newActivity = [];
        for ($i = 1; $i < 10; $i++) {
            $newActivity[$i] = [
                'OWNER_ID' => $contactId,
                'OWNER_TYPE_ID' => 3,
                'TYPE_ID' => ActivityType::call->value,
                'PROVIDER_ID' => 'VOXIMPLANT_CALL',
                'PROVIDER_TYPE_ID' => 'CALL',
                'SUBJECT' => sprintf('test activity - %s', $i),
                'DESCRIPTION' => 'test activity description',
                'DESCRIPTION_TYPE' => '1',
                'DIRECTION' => '2',
                'COMMUNICATIONS' => [
                    0 => [
                        'TYPE' => 'PHONE',
                        'VALUE' => DemoDataGenerator::getMobilePhone()->getNationalNumber(),
                    ],
                ],
            ];
            $this->activityId[] = $this->activityService->add($newActivity[$i])->getId();;
        }

        $activitiesResult = $this->activityService->list(
            ['ID' => 'DESC'],
            [
                'OWNER_ID' => $contactId,
            ],
            ["*", "COMMUNICATIONS"],
            0
        );


      $this->assertEquals(count($newActivity), count($activitiesResult->getActivities()));
    }

    /**
     * @throws BaseException
     * @throws TransportException
     */
    public function testUpdate(): void
    {
        $contactId = $this->contactService->add(['NAME' => 'test contact'])->getId();
        $this->contactId[] = $contactId;

        $newActivity = [
            'OWNER_ID' => $contactId,
            'OWNER_TYPE_ID' => 3,
            'TYPE_ID' => ActivityType::call->value,
            'PROVIDER_ID' => 'VOXIMPLANT_CALL',
            'PROVIDER_TYPE_ID' => 'CALL',
            'SUBJECT' => 'test activity',
            'DESCRIPTION' => 'test activity description',
            'DESCRIPTION_TYPE' => '1',
            'DIRECTION' => '2',
            'COMMUNICATIONS' => [
                0 => [
                    'TYPE' => 'PHONE',
                    'VALUE' => DemoDataGenerator::getMobilePhone()->getNationalNumber(),
                ],
            ],
        ];
        $activityId = $this->activityService->add($newActivity)->getId();
        $this->activityId[] = $activityId;

        $subject = 'qqqqq';
        $this->activityService->update($activityId, [
            'SUBJECT' => $subject,
        ]);

        $this->assertEquals($subject, $this->activityService->get($activityId)->activity()->SUBJECT);
    }

    /**
     * @throws \Bitrix24\SDK\Core\Exceptions\TransportException
     * @throws \Bitrix24\SDK\Core\Exceptions\BaseException
     */
    public function testCountByFilter(): void
    {
        $contactId = $this->contactService->add(['NAME' => 'test contact'])->getId();
        $this->contactId[] = $contactId;

        $newActivity = [];
        for ($i = 1; $i < 10; $i++) {
            $newActivity[$i] = [
                'OWNER_ID' => $contactId,
                'OWNER_TYPE_ID' => 3,
                'TYPE_ID' => ActivityType::call->value,
                'PROVIDER_ID' => 'VOXIMPLANT_CALL',
                'PROVIDER_TYPE_ID' => 'CALL',
                'SUBJECT' => sprintf('test activity - %s', $i),
                'DESCRIPTION' => 'test activity description',
                'DESCRIPTION_TYPE' => '1',
                'DIRECTION' => '2',
                'COMMUNICATIONS' => [
                    0 => [
                        'TYPE' => 'PHONE',
                        'VALUE' => DemoDataGenerator::getMobilePhone()->getNationalNumber(),
                    ],
                ],
            ];
            $this->activityId[] = $this->activityService->add($newActivity[$i])->getId();;
        }

        $this->assertEquals(
            count($newActivity),
            $this->activityService->countByFilter(
                [
                    'OWNER_ID' => $contactId,
                ]
            )
        );
    }

    protected function tearDown(): void
    {
    }

    protected function setUp(): void
    {
        $this->activityService = Fabric::getServiceBuilder()->getCRMScope()->activity();
        $this->contactService = Fabric::getServiceBuilder()->getCRMScope()->contact();
        $this->contactId = [];
        $this->activityId = [];
    }
}