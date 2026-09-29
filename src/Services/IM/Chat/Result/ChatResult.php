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

namespace Bitrix24\SDK\Services\IM\Chat\Result;

use Bitrix24\SDK\Core\Exceptions\BaseException;
use Bitrix24\SDK\Core\Result\AbstractResult;

class ChatResult extends AbstractResult
{
    /**
     * @throws BaseException
     */
    public function chat(): ?ChatItemResult
    {
        $result = $this->getCoreResponse()->getResponseData()->getResult();

        if (!array_key_exists('ID', $result)) {
            return null;
        }

        return new ChatItemResult($result);
    }
}
