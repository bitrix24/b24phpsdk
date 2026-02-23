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

namespace Bitrix24\SDK\Tests\Unit\Services\Task\Result;

use Bitrix24\SDK\Core\Response\DTO\ResponseData;
use Bitrix24\SDK\Core\Response\Response;
use Bitrix24\SDK\Services\Task\Result\AccessesResult;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;

#[CoversClass(AccessesResult::class)]
class AccessesResultTest extends TestCase
{
    public function testGetAccessesWithV3FlatActionMap(): void
    {
        $accessesResult = $this->makeResult([
            'read' => true,
            'edit' => false,
            'resultRead' => false,
            'sort' => true,
        ]);

        $items = $accessesResult->getAccesses();

        self::assertCount(1, $items);
        self::assertSame(0, $items[0]->getUserId());
        self::assertTrue($items[0]->read);
        self::assertFalse($items[0]->edit);
        self::assertFalse($items[0]->resultRead);
        self::assertTrue($items[0]->sort);
    }

    public function testGetAccessesWithEmptyResult(): void
    {
        $accessesResult = $this->makeResult([]);

        self::assertSame([], $accessesResult->getAccesses());
    }

    private function makeResult(array $payload): AccessesResult
    {
        $responseData = $this->createStub(ResponseData::class);
        $responseData->method('getResult')->willReturn($payload);

        $response = $this->createStub(Response::class);
        $response->method('getResponseData')->willReturn($responseData);

        return new AccessesResult($response);
    }
}
