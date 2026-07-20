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

namespace Bitrix24\SDK\Tests\Unit\Services\Mail\Service;

use Bitrix24\SDK\Core\Contracts\BatchOperationsInterface;
use Bitrix24\SDK\Core\Response\DTO\Pagination;
use Bitrix24\SDK\Core\Response\DTO\ResponseData;
use Bitrix24\SDK\Core\Response\DTO\Time;
use Bitrix24\SDK\Services\Mail\Result\MailboxItemResult;
use Bitrix24\SDK\Services\Mail\Result\SendMessageItemResult;
use Bitrix24\SDK\Services\Mail\Service\Batch;
use Carbon\CarbonImmutable;
use Generator;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use Psr\Log\NullLogger;

#[CoversClass(Batch::class)]
class BatchTest extends TestCase
{
    #[Test]
    public function testMailboxListDelegatesToTraversableList(): void
    {
        $batchOperations = $this->createMock(BatchOperationsInterface::class);
        $batchOperations->expects($this->once())
            ->method('getTraversableList')
            ->with('mail.mailbox.list', [], ['name' => 'work'], ['id', 'email'], 50)
            ->willReturn($this->yieldValues([
                ['id' => 1, 'name' => 'Work', 'email' => 'work@example.com'],
            ]));

        $items = iterator_to_array(
            (new Batch($batchOperations, new NullLogger()))->mailboxList(
                filter: ['name' => 'work'],
                select: ['id', 'email'],
                limit: 50
            )
        );

        $this->assertContainsOnlyInstancesOf(MailboxItemResult::class, $items);
    }

    #[Test]
    public function testMessageSendDelegatesToAddEntityItems(): void
    {
        $items = [
            [
                'from' => 'user@example.com',
                'to' => ['client@example.com'],
                'subject' => 'Contract',
                'body' => 'Hello.',
            ],
        ];
        $batchOperations = $this->createMock(BatchOperationsInterface::class);
        $batchOperations->expects($this->once())
            ->method('addEntityItems')
            ->with('mail.message.send', $items)
            ->willReturn($this->yieldValues([
                new ResponseData(
                    ['success' => true, 'to' => ['client@example.com']],
                    new Time(0, 0, 0, 0, 0, new CarbonImmutable(), new CarbonImmutable(), 0),
                    new Pagination()
                ),
            ]));

        $results = iterator_to_array((new Batch($batchOperations, new NullLogger()))->messageSend($items));

        $this->assertContainsOnlyInstancesOf(SendMessageItemResult::class, $results);
    }

    /**
     * @param list<mixed> $values
     */
    private function yieldValues(array $values): Generator
    {
        foreach ($values as $key => $value) {
            yield $key => $value;
        }
    }
}
