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

namespace Bitrix24\SDK\Services\IMOpenLines\Events\OnOpenLineMessageDelete;

use Bitrix24\SDK\Core\Result\AbstractItem;

/**
 * @property-read string $CONNECTOR Connector identifier
 * @property-read int $LINE Open line identifier
 * @property-read array $DATA
 */
class OnOpenLineMessageDeletePayload extends AbstractItem
{
    /**
     * @property-read array $im Object with deleted message info: chat_id, message_id
     * @property-read array $message Object with message information: id
     * @property-read array $chat Object with chat information: id
     */
    public function data(): OnOpenLineMessageDeleteDataItem
    {
        return new OnOpenLineMessageDeleteDataItem($this->data['DATA'][0]);
    }

    public function getConnector(): string
    {
        return $this->data['CONNECTOR'];
    }

    public function getLine(): int
    {
        return $this->data['LINE'];
    }
}

/**
 * @property-read array $im
 * @property-read array $message
 * @property-read array $chat
 */
class OnOpenLineMessageDeleteDataItem extends AbstractItem
{
    public function im(): OnOpenLineMessageDeleteImItem
    {
        return new OnOpenLineMessageDeleteImItem($this->data['im']);
    }

    public function message(): OnOpenLineMessageDeleteMessageItem
    {
        return new OnOpenLineMessageDeleteMessageItem($this->data['message']);
    }

    public function chat(): OnOpenLineMessageDeleteChatItem
    {
        return new OnOpenLineMessageDeleteChatItem($this->data['chat']);
    }
}

/**
 * @property-read int $chat_id Chat identifier
 * @property-read int $message_id Message identifier
 */
class OnOpenLineMessageDeleteImItem extends AbstractItem
{
}

/**
 * @property-read int $id Message identifier
 */
class OnOpenLineMessageDeleteMessageItem extends AbstractItem
{
}

/**
 * @property-read int $id Chat identifier
 */
class OnOpenLineMessageDeleteChatItem extends AbstractItem
{
}