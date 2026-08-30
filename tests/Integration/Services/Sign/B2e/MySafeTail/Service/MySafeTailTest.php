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

namespace Bitrix24\SDK\Tests\Integration\Services\Sign\B2e\MySafeTail\Service;

use Bitrix24\SDK\Core\Exceptions\BaseException;
use Bitrix24\SDK\Core\Exceptions\TransportException;
use Bitrix24\SDK\Services\Sign\B2e\MySafeTail\Result\MySafeTailItemResult;
use Bitrix24\SDK\Services\Sign\B2e\MySafeTail\Result\MySafeTailResult;
use Bitrix24\SDK\Services\Sign\B2e\MySafeTail\Service\MySafeTail;
use Bitrix24\SDK\Tests\Integration\Factory;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\Attributes\TestDox;
use PHPUnit\Framework\TestCase;

#[CoversClass(MySafeTail::class)]
class MySafeTailTest extends TestCase
{
    private MySafeTail $mySafeTailService;

    #[\Override]
    protected function setUp(): void
    {
        $this->mySafeTailService = Factory::getServiceBuilder(true)->getSignScope()->mySafeTail();
    }

    /**
     * @throws BaseException
     * @throws TransportException
     */
    #[Test]
    #[TestDox('sign.b2e.mysafe.tail returns MySafeTailResult with items array')]
    public function testTailReturnsMySafeTailResult(): void
    {
        $mySafeTailResult = $this->mySafeTailService->tail(20, 0);

        self::assertInstanceOf(MySafeTailResult::class, $mySafeTailResult);
        self::assertIsArray($mySafeTailResult->getItems());
    }

    /**
     * @throws BaseException
     * @throws TransportException
     */
    #[Test]
    #[TestDox('sign.b2e.mysafe.tail items are MySafeTailItemResult instances when not empty')]
    public function testTailItemsAreMySafeTailItemResults(): void
    {
        $items = $this->mySafeTailService->tail(20, 0)->getItems();

        if ($items === []) {
            $this->markTestSkipped('No signed documents found in company safe — cannot verify item type.');
        }

        self::assertInstanceOf(MySafeTailItemResult::class, $items[0]);
        self::assertIsInt($items[0]->id);
        self::assertIsString($items[0]->title);
        self::assertIsString($items[0]->role);
    }

    /**
     * @throws BaseException
     * @throws TransportException
     */
    #[Test]
    #[TestDox('sign.b2e.mysafe.tail respects limit parameter')]
    public function testTailRespectsLimitParameter(): void
    {
        $items = $this->mySafeTailService->tail(1, 0)->getItems();

        self::assertLessThanOrEqual(1, count($items));
    }
}
