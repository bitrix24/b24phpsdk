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

namespace Bitrix24\SDK\Tests\Unit\Services\Note\Collection\Result;

use Bitrix24\SDK\Core\ApiLevelErrorHandler;
use Bitrix24\SDK\Core\Commands\Command;
use Bitrix24\SDK\Core\Response\Response;
use Bitrix24\SDK\Services\Note\Collection\Result\CollectionResult;
use Bitrix24\SDK\Services\Note\Collection\Result\CollectionState;
use Carbon\CarbonImmutable;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use Psr\Log\NullLogger;
use Symfony\Component\HttpClient\MockHttpClient;
use Symfony\Component\HttpClient\Response\MockResponse;

#[CoversClass(CollectionResult::class)]
class CollectionResultTest extends TestCase
{
    #[Test]
    public function testItemCastsLifecycleFields(): void
    {
        $collection = $this->createResult([
            'id' => 334,
            'name' => 'Sales KB',
            'position' => 500,
            'policyLevel' => 'private',
            'accessLevel' => 'full',
            'state' => 'trashed',
            'isArchived' => false,
            'createdBy' => 1,
            'createdAt' => '2026-10-28T11:19:24+03:00',
            'updatedBy' => 1,
            'updatedAt' => '2026-10-28T11:19:24+03:00',
            'markdownDescription' => null,
        ])->collection();

        $this->assertSame(334, $collection->id);
        $this->assertSame(500, $collection->position);
        $this->assertSame('full', $collection->accessLevel);
        $this->assertSame(CollectionState::trashed, $collection->state);
        $this->assertFalse($collection->isArchived);
        $this->assertInstanceOf(CarbonImmutable::class, $collection->createdAt);
        $this->assertNull($collection->markdownDescription);
    }

    private function createResult(array $item): CollectionResult
    {
        return new CollectionResult(
            new Response(
                (new MockHttpClient(new MockResponse(json_encode(['result' => ['item' => $item]], JSON_THROW_ON_ERROR))))
                    ->request('POST', 'https://example.test'),
                new Command('note.collection.move', []),
                new ApiLevelErrorHandler(new NullLogger()),
                new NullLogger()
            )
        );
    }
}
