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

namespace Bitrix24\SDK\Tests\Integration\Services\Timeman\Record\Service;

use Bitrix24\SDK\Core\Contracts\SortOrder;
use Bitrix24\SDK\Core\Exceptions\BaseException;
use Bitrix24\SDK\Core\Exceptions\TransportException;
use Bitrix24\SDK\Services\Timeman\Record\Service\Record;
use Bitrix24\SDK\Services\Timeman\Record\Service\RecordFilter;
use Bitrix24\SDK\Services\Timeman\Record\Service\RecordSelectBuilder;
use Bitrix24\SDK\Tests\Integration\Factory;
use Carbon\CarbonImmutable;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\TestDox;
use PHPUnit\Framework\TestCase;

#[CoversClass(Record::class)]
class RecordTest extends TestCase
{
    private Record $recordService;

    private int $currentUserId;

    /**
     * @throws BaseException
     * @throws TransportException
     */
    #[\Override]
    protected function setUp(): void
    {
        $serviceBuilder = Factory::getServiceBuilder();
        $this->recordService = $serviceBuilder->getTimemanScope()->record();
        $this->currentUserId = (int)$serviceBuilder->getUserScope()->user()->current()->user()->ID;

        // ensure at least one work-time record exists for the current user
        $timemanService = $serviceBuilder->getTimemanScope()->timeman();
        $timemanService->open();
        $timemanService->close();
    }

    /**
     * @throws BaseException
     * @throws TransportException
     */
    #[TestDox('timeman.record.list returns work-time records with typed fields (builders)')]
    public function testList(): void
    {
        $recordsResult = $this->recordService->list(
            (new RecordSelectBuilder())->allSystemFields(),
            (new RecordFilter())->userId()->eq($this->currentUserId),
            ['startTime' => SortOrder::Descending],
            ['limit' => 5]
        );

        $records = $recordsResult->getRecords();
        $this->assertIsArray($records);

        if ($records !== []) {
            $recordItemResult = $records[0];
            $this->assertGreaterThan(0, $recordItemResult->id);
            $this->assertSame($this->currentUserId, $recordItemResult->userId);
            $this->assertInstanceOf(CarbonImmutable::class, $recordItemResult->startTime);
        }
    }

    /**
     * @throws BaseException
     * @throws TransportException
     */
    #[TestDox('timeman.record.list returns work-time records with array arguments')]
    public function testListWithArrayArguments(): void
    {
        $recordsResult = $this->recordService->list(
            ['id', 'userId', 'startTime', 'duration'],
            [['userId', $this->currentUserId]],
            [],
            ['limit' => 3]
        );

        $this->assertIsArray($recordsResult->getRecords());
    }
}
