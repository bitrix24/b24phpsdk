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

class PublicChannelResult extends AbstractResult
{
    /**
     * @throws BaseException
     */
    public function getChannel(): PublicChannelItemResult
    {
        return new PublicChannelItemResult($this->getCoreResponse()->getResponseData()->getResult());
    }
}
