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

namespace Bitrix24\SDK\Services\IMOpenLines\Message\Result;

use Bitrix24\SDK\Core\Result\AbstractResult;

/**
 * Class CrmMessageAddResult
 *
 * Result class for imopenlines.crm.message.add
 *
 * @package Bitrix24\SDK\Services\IMOpenLines\Message\Result
 */
class CrmMessageAddResult extends AbstractResult
{
    /**
     * Get the identifier of the created message in the chat
     */
    public function getMessageId(): int
    {
        return (int)$this->getCoreResponse()->getResponseData()->getResult();
    }
}
