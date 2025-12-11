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

namespace Bitrix24\SDK\Services\IMOpenLines\Events\OnSessionStart;

use Bitrix24\SDK\Core\Result\AbstractItem;

/**
 * @property-read array $DATA
 */
class OnSessionStartPayload extends AbstractItem
{
    /**
     * @property-read array $connector Object with connector information: connector_id, line_id, chat_id, user_id
     * @property-read array $chat Object with chat information: id
     * @property-read array $user Object with user information: id, name
     * @property-read array $line Object with open line information: id, name  
     */
    public function data(): OnSessionStartDataItem
    {
        return new OnSessionStartDataItem($this->data['DATA'][0]);
    }
}

/**
 * @property-read array $connector
 * @property-read array $chat
 * @property-read array $user
 * @property-read array $line
 */
class OnSessionStartDataItem extends AbstractItem
{
    public function connector(): OnSessionStartConnectorItem
    {
        return new OnSessionStartConnectorItem($this->data['connector']);
    }

    public function chat(): OnSessionStartChatItem
    {
        return new OnSessionStartChatItem($this->data['chat']);
    }

    public function user(): OnSessionStartUserItem
    {
        return new OnSessionStartUserItem($this->data['user']);
    }

    public function line(): OnSessionStartLineItem
    {
        return new OnSessionStartLineItem($this->data['line']);
    }
}

/**
 * @property-read string $connector_id Connector identifier
 * @property-read int $line_id Open line identifier
 * @property-read int $chat_id Chat identifier
 * @property-read int $user_id User identifier in external system
 */
class OnSessionStartConnectorItem extends AbstractItem
{
}

/**
 * @property-read int $id Chat identifier
 */
class OnSessionStartChatItem extends AbstractItem
{
}

/**
 * @property-read int $id User identifier
 * @property-read string $name User name
 */
class OnSessionStartUserItem extends AbstractItem
{
}

/**
 * @property-read int $id Open line identifier
 * @property-read string $name Open line name
 */
class OnSessionStartLineItem extends AbstractItem
{
}