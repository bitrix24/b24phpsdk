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

namespace Bitrix24\SDK\Services\IMOpenLines\Events\OnOpenLineMessageUpdate;

use Bitrix24\SDK\Core\Result\AbstractItem;

/**
 * @property-read string $CONNECTOR Connector identifier
 * @property-read int $LINE Open line identifier
 * @property-read array $DATA
 */
class OnOpenLineMessageUpdatePayload extends AbstractItem
{
    /**
     * @property-read array $im Object with modified message info: chat_id, message_id
     * @property-read array $message Object with message information: id
     * @property-read array $chat Object with chat information: id
     */
    public function data(): OnOpenLineMessageUpdateDataItem
    {
        return new OnOpenLineMessageUpdateDataItem($this->data['DATA'][0]);
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
class OnOpenLineMessageUpdateDataItem extends AbstractItem
{
    public function im(): OnOpenLineMessageUpdateImItem
    {
        return new OnOpenLineMessageUpdateImItem($this->data['im']);
    }

    public function message(): OnOpenLineMessageUpdateMessageItem
    {
        return new OnOpenLineMessageUpdateMessageItem($this->data['message']);
    }

    public function chat(): OnOpenLineMessageUpdateChatItem
    {
        return new OnOpenLineMessageUpdateChatItem($this->data['chat']);
    }
}

/**
 * @property-read int $chat_id Chat identifier
 * @property-read int $message_id Message identifier
 */
class OnOpenLineMessageUpdateImItem extends AbstractItem
{
}

/**
 * @property-read int $id Message identifier
 */
class OnOpenLineMessageUpdateMessageItem extends AbstractItem
{
}

/**
 * @property-read int $id Chat identifier
 */
class OnOpenLineMessageUpdateChatItem extends AbstractItem
{
}