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

namespace Bitrix24\SDK\Tests\Unit\Services\CRM\Lead\Service;

use Bitrix24\SDK\Core\Contracts\BatchOperationsInterface;
use Bitrix24\SDK\Services\CRM\Lead\Result\LeadItemResult;
use Bitrix24\SDK\Services\CRM\Lead\Service\Batch;
use Generator;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\Attributes\TestDox;
use PHPUnit\Framework\TestCase;
use Psr\Log\NullLogger;

#[CoversClass(Batch::class)]
class BatchTest extends TestCase
{
    #[Test]
    #[TestDox('list() yields LeadItemResult items')]
    public function testListYieldsLeadItemResults(): void
    {
        $batchOperations = $this->createMock(BatchOperationsInterface::class);
        $batchOperations->expects($this->once())
            ->method('getTraversableList')
            ->with('crm.lead.list', ['ID' => 'ASC'], ['ID' => 42], ['ID', 'TITLE'], 1)
            ->willReturn($this->yieldValues([
                ['ID' => 42, 'TITLE' => 'Test lead'],
            ]));

        $items = iterator_to_array(
            (new Batch($batchOperations, new NullLogger()))->list(
                ['ID' => 'ASC'],
                ['ID' => 42],
                ['ID', 'TITLE'],
                1
            )
        );

        $this->assertContainsOnlyInstancesOf(LeadItemResult::class, $items);
    }

    /**
     * @param list<array<string, mixed>> $values
     */
    private function yieldValues(array $values): Generator
    {
        foreach ($values as $key => $value) {
            yield $key => $value;
        }
    }
}
