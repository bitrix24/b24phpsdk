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
 * Class ChatLastIdResult
 *
 * Result class for imopenlines.crm.chat.getLastId
 *
 * @package Bitrix24\SDK\Services\IMOpenLines\CRMChat\Result
 */
class ChatLastIdResult extends AbstractResult
{
    /**
     * Return the last chat ID
     */
    public function getLastChatId(): string
    {
        $result = $this->getCoreResponse()->getResponseData()->getResult();
        
        // According to docs, this returns a scalar value, but SDK converts it to array
        return is_array($result) ? (string)$result[0] : (string)$result;
    }
}