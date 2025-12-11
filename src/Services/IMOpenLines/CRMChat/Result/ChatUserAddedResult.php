<?php

/**
 * This file is part of the bitrix24-php-sdk package.
 *
 * © Sally Fancen <vadimsallee@gmail.com>
 *
 * For the full copyright and license information, please view the MIT-LICENSE.txt
 * file that was distributed with this source code.
 */

declare(strict_types=1);

namespace Bitrix24\SDK\Services\IMOpenLines\CRMChat\Result;

use Bitrix24\SDK\Core\Result\AbstractResult;

/**
 * Class ChatUserAddedResult
 *
 * Result class for imopenlines.crm.chat.user.add
 *
 * @package Bitrix24\SDK\Services\IMOpenLines\CRMChat\Result
 */
class ChatUserAddedResult extends AbstractResult
{
    /**
     * Return the chat ID where user was added
     */
    public function getChatId(): int
    {
        $result = $this->getCoreResponse()->getResponseData()->getResult();
        
        return (int)$result[0];
    }
}