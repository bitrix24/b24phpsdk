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

namespace Bitrix24\SDK\Services\Pull\Channel\Service;

use Bitrix24\SDK\Attributes\ApiEndpointMetadata;
use Bitrix24\SDK\Attributes\ApiServiceMetadata;
use Bitrix24\SDK\Core\Credentials\Scope;
use Bitrix24\SDK\Core\Exceptions\BaseException;
use Bitrix24\SDK\Services\AbstractService;
use Bitrix24\SDK\Services\Pull\Channel\Result\PublicChannelResult;
use Bitrix24\SDK\Services\Pull\Channel\Result\PublicChannelsResult;

#[ApiServiceMetadata(new Scope(['pull_channel']))]
class Channel extends AbstractService
{
    /** @throws BaseException */
    #[ApiEndpointMetadata('pull.channel.public.get', 'https://github.com/bitrix24/b24jssdk/blob/main/packages/jssdk/src/pullClient/channel-manager.ts', 'Returns a public channel descriptor.')]
    public function get(?int $userId = null, bool $application = false): PublicChannelResult
    {
        $parameters = ['APPLICATION' => $application ? 'Y' : 'N'];
        if ($userId !== null) {
            $parameters['USER_ID'] = $userId;
        }

        return new PublicChannelResult($this->core->call('pull.channel.public.get', $parameters));
    }

    /**
     * @param int[] $userIds
     * @throws BaseException
     */
    #[ApiEndpointMetadata('pull.channel.public.list', 'https://github.com/bitrix24/b24jssdk/blob/main/packages/jssdk/src/pullClient/channel-manager.ts', 'Returns public channels keyed by user ID.')]
    public function list(array $userIds, bool $application = false): PublicChannelsResult
    {
        return new PublicChannelsResult($this->core->call('pull.channel.public.list', ['USERS' => $userIds, 'APPLICATION' => $application ? 'Y' : 'N']));
    }
}
