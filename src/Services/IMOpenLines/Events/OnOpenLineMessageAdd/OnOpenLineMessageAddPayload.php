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

namespace Bitrix24\SDK\Services\IMOpenLines\Events\OnOpenLineMessageAdd;

use Bitrix24\SDK\Core\Result\AbstractItem;

/**
 * @property-read array $DATA
 */
class OnOpenLineMessageAddPayload extends AbstractItem
{
    /**
     * @property-read array $connector Object with connector information: connector_id, line_id, chat_id, user_id
     * @property-read array $chat Object with chat information: id
     * @property-read array $message Object with message information: id, date, text, files, attach, system, user_id
     * @property-read array $ref Tracker code trackId for linking message to CRM object
     * @property-read array $extra Object with additional information: EXTRA_URL
     */
    public function data(): OnOpenLineMessageAddDataItem
    {
        return new OnOpenLineMessageAddDataItem($this->data['DATA'][0]);
    }
}

/**
 * @property-read array $connector
 * @property-read array $chat
 * @property-read array $message
 * @property-read array $ref
 * @property-read array $extra
 */
class OnOpenLineMessageAddDataItem extends AbstractItem
{
    public function connector(): OnOpenLineMessageAddConnectorItem
    {
        return new OnOpenLineMessageAddConnectorItem($this->data['connector']);
    }

    public function chat(): OnOpenLineMessageAddChatItem
    {
        return new OnOpenLineMessageAddChatItem($this->data['chat']);
    }

    public function message(): OnOpenLineMessageAddMessageItem
    {
        return new OnOpenLineMessageAddMessageItem($this->data['message']);
    }

    public function ref(): OnOpenLineMessageAddRefItem
    {
        return new OnOpenLineMessageAddRefItem($this->data['ref']);
    }

    public function extra(): OnOpenLineMessageAddExtraItem
    {
        return new OnOpenLineMessageAddExtraItem($this->data['extra']);
    }
}

/**
 * @property-read string $connector_id Connector identifier
 * @property-read int $line_id Open line identifier
 * @property-read int $chat_id Chat identifier
 * @property-read int $user_id User identifier in external system
 */
class OnOpenLineMessageAddConnectorItem extends AbstractItem
{
}

/**
 * @property-read int $id Chat identifier
 */
class OnOpenLineMessageAddChatItem extends AbstractItem
{
}

/**
 * @property-read int $id Message identifier
 * @property-read string $date Date and time of message addition
 * @property-read string $text Message text
 * @property-read array $files Files attached to message
 * @property-read string $attach Attached files
 * @property-read string $system Flag indicating if message is system (Y/N)
 * @property-read int $user_id User identifier
 */
class OnOpenLineMessageAddMessageItem extends AbstractItem
{
}

/**
 * @property-read mixed $trackId Tracker code for linking message to CRM object
 */
class OnOpenLineMessageAddRefItem extends AbstractItem
{
}

/**
 * @property-read string $EXTRA_URL External link for Bitrix24.Network
 */
class OnOpenLineMessageAddExtraItem extends AbstractItem
{
}