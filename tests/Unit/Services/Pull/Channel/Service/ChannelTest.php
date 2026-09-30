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

namespace Bitrix24\SDK\Tests\Unit\Services\Pull\Channel\Service;

use Bitrix24\SDK\Services\Pull\Channel\Service\Channel;
use Bitrix24\SDK\Tests\Unit\Services\Pull\AbstractServiceContractTestCase;

class ChannelTest extends AbstractServiceContractTestCase
{
    public static function calls(): array
    {
        return [
            'channel defaults' => [Channel::class, 'get', [], 'pull.channel.public.get', ['APPLICATION' => 'N']],
            'application channel' => [Channel::class, 'get', [12, true], 'pull.channel.public.get', ['USER_ID' => 12, 'APPLICATION' => 'Y']],
            'list' => [Channel::class, 'list', [[12,12], true], 'pull.channel.public.list', ['USERS' => [12,12], 'APPLICATION' => 'Y']],
            'empty list' => [Channel::class, 'list', [[]], 'pull.channel.public.list', ['USERS' => [], 'APPLICATION' => 'N']],
        ];
    }
}
