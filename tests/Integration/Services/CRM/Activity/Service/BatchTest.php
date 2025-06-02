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
use Bitrix24\SDK\Services\CRM\Activity\Service\Activity;
use Bitrix24\SDK\Services\CRM\Contact\Service\Contact;
use Bitrix24\SDK\Tests\Builders\DemoDataGenerator;
use Bitrix24\SDK\Tests\Integration\Fabric;
use PHPUnit\Framework\TestCase;

#[\PHPUnit\Framework\Attributes\CoversClass(\Bitrix24\SDK\Services\CRM\Activity\Service\Batch::class)]
#[\PHPUnit\Framework\Attributes\CoversClass(\Bitrix24\SDK\Services\CRM\Contact\Service\Batch::class)]
class BatchTest extends TestCase
{
    private Contact $contactService;

    private Activity $activityService;

    private const BATCH_TEST_ELEMENTS_COUNT = 60;

    private array $contactId;

    /**
     * @throws \Bitrix24\SDK\Core\Exceptions\BaseException
     */
    #[\PHPUnit\Framework\Attributes\TestDox('Batch add deals')]
    public function testBatchAdd(): void
    {
        $contactId = $this->contactService->add(['NAME' => 'test contact'])->getId();
        $this->contactId[] = $contactId;

        $items = [];
        for ($i = 1; $i < self::BATCH_TEST_ELEMENTS_COUNT; $i++) {
            $items[] = [
                'OWNER_ID'         => $contactId,
                'OWNER_TYPE_ID'    => 3,
                'TYPE_ID'          => 2,
                'PROVIDER_ID'      => 'VOXIMPLANT_CALL',
                'PROVIDER_TYPE_ID' => 'CALL',
                'SUBJECT'          => 'test activity',
                'DESCRIPTION'      => 'test activity description',
                'DESCRIPTION_TYPE' => '1',
                'DIRECTION'        => '2',
                'COMMUNICATIONS'   => [
                    0 => [
                        'TYPE'  => 'PHONE',
                        'VALUE' => DemoDataGenerator::getMobilePhone()->getNationalNumber(),
                    ],
                ],
            ];
        }

        $cnt = 0;
        $activityId = [];
        foreach ($this->activityService->batch->add($items) as $item) {
            $cnt++;
            $activityId[] = $item->getId();
        }

        self::assertEquals(count($items), $cnt);

        $cnt = 0;
        foreach ($this->activityService->batch->delete($activityId) as $cnt => $deleteResult) {
            $cnt++;
        }

        self::assertEquals(count($items), $cnt);
    }

    /**
     * @throws \Bitrix24\SDK\Core\Exceptions\BaseException
     */
    #[\PHPUnit\Framework\Attributes\TestDox('Batch delete activities')]
    public function testBatchDelete(): void
    {
        $contactId = $this->contactService->add(['NAME' => 'test contact'])->getId();
        $this->contactId[] = $contactId;

        $items = [];
        for ($i = 1; $i < self::BATCH_TEST_ELEMENTS_COUNT; $i++) {
            $items[] = [
                'OWNER_ID'         => $contactId,
                'OWNER_TYPE_ID'    => 3,
                'TYPE_ID'          => 2,
                'PROVIDER_ID'      => 'VOXIMPLANT_CALL',
                'PROVIDER_TYPE_ID' => 'CALL',
                'SUBJECT'          => 'test activity',
                'DESCRIPTION'      => 'test activity description',
                'DESCRIPTION_TYPE' => '1',
                'DIRECTION'        => '2',
                'COMMUNICATIONS'   => [
                    0 => [
                        'TYPE'  => 'PHONE',
                        'VALUE' => DemoDataGenerator::getMobilePhone()->getNationalNumber(),
                    ],
                ],
            ];
        }

        $cnt = 0;
        $activityId = [];
        foreach ($this->activityService->batch->add($items) as $item) {
            $cnt++;
            $activityId[] = $item->getId();
        }

        self::assertEquals(count($items), $cnt);

        $cnt = 0;
        foreach ($this->activityService->batch->delete($activityId) as $cnt => $deleteResult) {
            $cnt++;
        }

        self::assertEquals(count($items), $cnt);


        $this->assertEquals(
            0,
            $this->activityService->countByFilter(
                [
                    'OWNER_ID' => $contactId,
                ]
            )
        );
    }

    /**
     * @throws BaseException
     * @throws TransportException
     */
    #[\PHPUnit\Framework\Attributes\TestDox('Batch list deals')]
    public function testBatchList(): void
    {
        $contactId = $this->contactService->add(['NAME' => 'test contact'])->getId();
        $this->contactId[] = $contactId;

        $items = [];
        for ($i = 1; $i < self::BATCH_TEST_ELEMENTS_COUNT; $i++) {
            $items[] = [
                'OWNER_ID'         => $contactId,
                'OWNER_TYPE_ID'    => 3,
                'TYPE_ID'          => 2,
                'PROVIDER_ID'      => 'VOXIMPLANT_CALL',
                'PROVIDER_TYPE_ID' => 'CALL',
                'SUBJECT'          => 'test activity',
                'DESCRIPTION'      => 'test activity description',
                'DESCRIPTION_TYPE' => '1',
                'DIRECTION'        => '2',
                'COMMUNICATIONS'   => [
                    0 => [
                        'TYPE'  => 'PHONE',
                        'VALUE' => DemoDataGenerator::getMobilePhone()->getNationalNumber(),
                    ],
                ],
            ];
        }

        $cnt = 0;
        $activityId = [];
        foreach ($this->activityService->batch->add($items) as $item) {
            $cnt++;
            $activityId[] = $item->getId();
        }

        //fetch items
        $itemsCnt = 0;
        foreach ($this->activityService->batch->list(['ID' => 'DESC'], ['OWNER_ID' => $contactId], ['*']) as $item) {
            $itemsCnt++;
        }

        $this->assertEquals(
            count($activityId),
            $itemsCnt,
            sprintf(
                'batch activity list not fetched, expected %s, actual %s',
                count($activityId),
                $itemsCnt
            )
        );
    }

    protected function tearDown(): void
    {
        $this->contactService->batch->delete($this->contactId);
    }

    protected function setUp(): void
    {
        $this->activityService = Fabric::getServiceBuilder()->getCRMScope()->activity();
        $this->contactService = Fabric::getServiceBuilder()->getCRMScope()->contact();
        $this->contactId = [];
    }
}