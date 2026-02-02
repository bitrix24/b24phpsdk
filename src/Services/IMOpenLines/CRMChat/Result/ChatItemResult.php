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

use Bitrix24\SDK\Core\Result\AbstractItem;

/**
 * Class ChatItemResult
 *
 * Represents a single chat item
 * 
 * @property-read string $CHAT_ID Identifier of the chat
 * @property-read string $CONNECTOR_ID Identifier of the connector
 * @property-read string $CONNECTOR_TITLE Title of the connector
 *
 * @package Bitrix24\SDK\Services\IMOpenLines\CRMChat\Result
 */
class ChatItemResult extends AbstractItem
{
}