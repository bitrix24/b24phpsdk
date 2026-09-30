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

namespace Bitrix24\SDK\Services\Pull\Watch\Service;

use Bitrix24\SDK\Attributes\ApiEndpointMetadata;
use Bitrix24\SDK\Attributes\ApiServiceMetadata;
use Bitrix24\SDK\Core\Credentials\Scope;
use Bitrix24\SDK\Core\Exceptions\BaseException;
use Bitrix24\SDK\Services\AbstractService;
use Bitrix24\SDK\Services\Pull\Watch\Result\WatchTagsResult;

#[ApiServiceMetadata(new Scope(['pull']))]
class Watch extends AbstractService
{
    /**
     * @param array<int, int|string> $tags
     * @throws BaseException
     */
    #[ApiEndpointMetadata('pull.watch.extend', 'https://github.com/bitrix24/b24jssdk/blob/main/packages/jssdk/src/pullClient/client.ts', 'Extends watched tags and returns their IDs.')]
    public function extend(array $tags): WatchTagsResult
    {
        return new WatchTagsResult($this->core->call('pull.watch.extend', ['tags' => $tags]));
    }
}
