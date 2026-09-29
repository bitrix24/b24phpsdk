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

namespace Bitrix24\SDK\Services\Pull\Channel\Result;

use Bitrix24\SDK\Core\Result\AbstractResult;
use Bitrix24\SDK\Core\Exceptions\BaseException;

class PublicChannelsResult extends AbstractResult
{
    /**
     * @return array<int, PublicChannelItemResult>
     * @throws BaseException
     */
    public function getChannels(): array
    {
        return array_map(static fn (array $item): PublicChannelItemResult => new PublicChannelItemResult($item), $this->getCoreResponse()->getResponseData()->getResult());
    }
}
