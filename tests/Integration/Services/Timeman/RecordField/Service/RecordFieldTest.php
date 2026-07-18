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

namespace Bitrix24\SDK\Tests\Integration\Services\Timeman\RecordField\Service;

use Bitrix24\SDK\Core\Exceptions\BaseException;
use Bitrix24\SDK\Core\Exceptions\TransportException;
use Bitrix24\SDK\Services\Timeman\RecordField\Result\RecordFieldItemResult;
use Bitrix24\SDK\Services\Timeman\RecordField\Service\RecordField;
use Bitrix24\SDK\Tests\Integration\Factory;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\TestDox;
use PHPUnit\Framework\TestCase;

#[CoversClass(RecordField::class)]
class RecordFieldTest extends TestCase
{
    private RecordField $recordFieldService;

    #[\Override]
    protected function setUp(): void
    {
        $this->recordFieldService = Factory::getServiceBuilder()->getTimemanScope()->recordField();
    }

    /**
     * @throws BaseException
     * @throws TransportException
     */
    #[TestDox('timeman.record.field.get returns a single field descriptor by name')]
    public function testGet(): void
    {
        $recordFieldItemResult = $this->recordFieldService->get('startTime')->recordField();

        $this->assertSame('startTime', $recordFieldItemResult->name);
        $this->assertIsBool($recordFieldItemResult->filterable);
        $this->assertIsBool($recordFieldItemResult->sortable);
    }

    /**
     * @throws BaseException
     * @throws TransportException
     */
    #[TestDox('timeman.record.field.list returns all field descriptors')]
    public function testList(): void
    {
        $recordFields = $this->recordFieldService->list()->getRecordFields();

        $this->assertNotEmpty($recordFields);
        $this->assertContainsOnlyInstancesOf(RecordFieldItemResult::class, $recordFields);
    }
}
