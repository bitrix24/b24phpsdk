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

namespace Bitrix24\SDK\Tests\Integration\Services\Sign\B2e\PersonalTail\Service;

use Bitrix24\SDK\Core\Exceptions\BaseException;
use Bitrix24\SDK\Core\Exceptions\TransportException;
use Bitrix24\SDK\Services\Sign\B2e\PersonalTail\Result\PersonalTailItemResult;
use Bitrix24\SDK\Services\Sign\B2e\PersonalTail\Result\PersonalTailResult;
use Bitrix24\SDK\Services\Sign\B2e\PersonalTail\Service\PersonalTail;
use Bitrix24\SDK\Tests\Integration\Fabric;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\Attributes\TestDox;
use PHPUnit\Framework\TestCase;

#[CoversClass(PersonalTail::class)]
class PersonalTailTest extends TestCase
{
    private PersonalTail $personalTailService;

    #[\Override]
    protected function setUp(): void
    {
        $this->personalTailService = Fabric::getServiceBuilder(true)->getSignScope()->personalTail();
    }

    /**
     * @throws BaseException
     * @throws TransportException
     */
    #[Test]
    #[TestDox('sign.b2e.personal.tail returns PersonalTailResult with items array')]
    public function testTailReturnsPersonalTailResult(): void
    {
        $personalTailResult = $this->personalTailService->tail(20, 0);

        self::assertInstanceOf(PersonalTailResult::class, $personalTailResult);
        self::assertIsArray($personalTailResult->getItems());
    }

    /**
     * @throws BaseException
     * @throws TransportException
     */
    #[Test]
    #[TestDox('sign.b2e.personal.tail items are PersonalTailItemResult instances when not empty')]
    public function testTailItemsArePersonalTailItemResults(): void
    {
        $items = $this->personalTailService->tail(20, 0)->getItems();

        if ($items === []) {
            $this->markTestSkipped('No signed documents found in personal tail — cannot verify item type.');
        }

        self::assertInstanceOf(PersonalTailItemResult::class, $items[0]);
        self::assertIsInt($items[0]->id);
        self::assertIsString($items[0]->title);
    }

    /**
     * @throws BaseException
     * @throws TransportException
     */
    #[Test]
    #[TestDox('sign.b2e.personal.tail respects limit parameter')]
    public function testTailRespectsLimitParameter(): void
    {
        $items = $this->personalTailService->tail(1, 0)->getItems();

        self::assertLessThanOrEqual(1, count($items));
    }
}
