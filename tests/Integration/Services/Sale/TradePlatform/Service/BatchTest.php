<?php

/**
 * This file is part of the bitrix24-php-sdk package.
 *
 * © Dmitriy Ignatenko <algonexys@gmail.com>
 *
 * For the full copyright and license information, please view the MIT-LICENSE.txt
 * file that was distributed with this source code.
 */

declare(strict_types=1);

namespace Bitrix24\SDK\Tests\Integration\Services\Sale\TradePlatform\Service;

use Bitrix24\SDK\Core\Exceptions\BaseException;
use Bitrix24\SDK\Core\Exceptions\TransportException;
use Bitrix24\SDK\Services\Sale\TradePlatform\Result\TradePlatformItemResult;
use Bitrix24\SDK\Services\Sale\TradePlatform\Service\Batch;
use Bitrix24\SDK\Services\Sale\TradePlatform\Service\TradePlatform;
use Bitrix24\SDK\Tests\Integration\Fabric;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\TestDox;
use PHPUnit\Framework\TestCase;

#[CoversClass(Batch::class)]
#[CoversClass(\Bitrix24\SDK\Services\Sale\TradePlatform\Batch::class)]
class BatchTest extends TestCase
{
    private TradePlatform $tradePlatformService;

    #[\Override]
    protected function setUp(): void
    {
        $this->tradePlatformService = Fabric::getServiceBuilder()->getSaleScope()->tradePlatform();
    }

    /**
     * @throws BaseException
     * @throws TransportException
     */
    #[TestDox('Batch::list order sources')]
    public function testList(): void
    {
        $expectedIds = [];
        foreach ($this->tradePlatformService->list(['id'])->getTradePlatforms() as $tradePlatformItemResult) {
            $expectedIds[] = (int)$tradePlatformItemResult->id;
        }

        $listedIds = [];
        foreach ($this->tradePlatformService->batch->list(['id', 'code']) as $item) {
            $this->assertInstanceOf(TradePlatformItemResult::class, $item);
            $listedIds[] = (int)$item->id;
        }

        sort($expectedIds);
        sort($listedIds);
        $this->assertSame($expectedIds, $listedIds);
    }
}
