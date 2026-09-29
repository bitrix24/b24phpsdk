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

namespace Bitrix24\SDK\Tests\Integration\Services\Pull\Channel\Service;

use Bitrix24\SDK\Services\Pull\Channel\Service\Channel;
use Bitrix24\SDK\Tests\Integration\Services\Pull\Factory;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;

#[CoversClass(Channel::class)]
class ChannelTest extends TestCase
{
    public function testGetAndListPreserveUserKeys(): void
    {
        $channel = Factory::getServiceBuilder()->getPullScope()->channel();
        $item = $channel->get()->getChannel();
        self::assertGreaterThan(0, $item->user_id);
        $items = $channel->list([$item->user_id,$item->user_id])->getChannels();
        self::assertSame([$item->user_id], array_keys($items));
        self::assertSame($item->user_id, $items[$item->user_id]->user_id);
    }
}
