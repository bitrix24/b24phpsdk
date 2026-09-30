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

namespace Bitrix24\SDK\Tests\Integration\Services\Timeman\NetworkRange\Service;

use Bitrix24\SDK\Services\Timeman\NetworkRange\Service\NetworkRange;
use Bitrix24\SDK\Tests\Integration\Services\Timeman\ReadOnlyCoreTrait;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;
use Psr\Log\NullLogger;

#[CoversClass(NetworkRange::class)]
class NetworkRangeTest extends TestCase
{
    use ReadOnlyCoreTrait;
    public function testGet(): void
    {
        $networkRange = new NetworkRange($this->getReadOnlyCore(), new NullLogger());
        $result = $networkRange->get();
        self::assertIsArray($result->getRanges());
    }

    public function testCheck(): void
    {
        $networkRange = new NetworkRange($this->getReadOnlyCore(), new NullLogger());
        $result = $networkRange->check('127.0.0.1');
        self::assertTrue(!$result->getRange() instanceof \Bitrix24\SDK\Services\Timeman\NetworkRange\Result\NetworkRangeMatchItemResult || $result->getRange()->ip === '127.0.0.1');
    }

}
