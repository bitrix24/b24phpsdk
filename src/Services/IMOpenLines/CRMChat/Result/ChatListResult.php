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
 * Class ChatListResult
 *
 * Result class for imopenlines.crm.chat.get
 *
 * @package Bitrix24\SDK\Services\IMOpenLines\CRMChat\Result
 */
class ChatListResult extends AbstractResult
{
    /**
     * Return array of chat items
     *
     * @return ChatItemResult[]
     */
    public function getChats(): array
    {
        $result = $this->getCoreResponse()->getResponseData()->getResult();
        
        $chats = [];
        foreach ($result as $chat) {
            $chats[] = new ChatItemResult($chat);
        }
        
        return $chats;
    }
}