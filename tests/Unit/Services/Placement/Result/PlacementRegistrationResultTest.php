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

namespace Bitrix24\SDK\Tests\Unit\Services\Placement\Result;

use Bitrix24\SDK\Core\Exceptions\BaseException;
use Bitrix24\SDK\Services\Placement\Common\PlacementHandlerMetadata;
use Bitrix24\SDK\Services\Placement\PlacementRegistrationStatus;
use Bitrix24\SDK\Services\Placement\Result\PlacementRegistrationItemResult;
use Bitrix24\SDK\Services\Placement\Result\PlacementRegistrationResult;
use PHPUnit\Framework\TestCase;

class PlacementRegistrationResultTest extends TestCase
{
    public function testEmptyAndSuccessfulResultsSucceedInInputOrder(): void
    {
        self::assertTrue((new PlacementRegistrationResult([]))->isSuccess());
        $declaration = new PlacementHandlerMetadata('CODE', 'https://example.com/');
        $items = array_map(static fn (PlacementRegistrationStatus $status): PlacementRegistrationItemResult => new PlacementRegistrationItemResult($declaration, $status), [PlacementRegistrationStatus::created, PlacementRegistrationStatus::skipped, PlacementRegistrationStatus::updated]);
        $result = new PlacementRegistrationResult($items);
        self::assertSame($items, $result->getItems());
        self::assertTrue($result->isSuccess());
        self::assertNull($items[0]->error);
        self::assertNull($items[0]->rollbackError);
        self::assertNull($items[0]->previousConfigurationRestored);
    }

    public function testFailedUpdateRemainsFailedAfterSuccessfulRestoration(): void
    {
        $declaration = new PlacementHandlerMetadata('CODE', 'https://example.com/');
        $error = new BaseException('new bind failed');
        $rollbackError = new BaseException('rollback failed');
        $item = new PlacementRegistrationItemResult($declaration, PlacementRegistrationStatus::failed, $error, $rollbackError, true);
        self::assertSame($declaration, $item->declaration);
        self::assertSame($error, $item->error);
        self::assertSame($rollbackError, $item->rollbackError);
        self::assertTrue($item->previousConfigurationRestored);
        self::assertFalse((new PlacementRegistrationResult([$item]))->isSuccess());
    }
}
