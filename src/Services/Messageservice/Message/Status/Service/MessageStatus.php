<?php

/**
 * This file is part of the bitrix24-php-sdk package.
 *
 * © Dmitriy Ignatenko <algonexys@gmail.com>
 *
 * For the full copyright and license information, please view the MIT-LICENSE.txt
 * file that was distributed with this source code.
 */

declare(strict_types=1);

namespace Bitrix24\SDK\Services\Messageservice\Message\Status\Service;

use Bitrix24\SDK\Attributes\ApiEndpointMetadata;
use Bitrix24\SDK\Attributes\ApiServiceMetadata;
use Bitrix24\SDK\Core\Credentials\Scope;
use Bitrix24\SDK\Core\Exceptions\BaseException;
use Bitrix24\SDK\Core\Exceptions\TransportException;
use Bitrix24\SDK\Services\AbstractService;
use Bitrix24\SDK\Services\Messageservice\Message\Status\Result\MessageStatusUpdateResult;

#[ApiServiceMetadata(new Scope(['messageservice']))]
class MessageStatus extends AbstractService
{
    /**
     * Update delivery status of a message sent via a message service provider.
     *
     * Supported status values:
     * - queued — message is queued for sending
     * - sent — message was sent by the provider
     * - delivered — message was successfully delivered to the recipient
     * - undelivered — message was not delivered to the recipient
     * - failed — provider encountered a sending or processing error
     *
     * @param string $code Sender code. Can be retrieved via messageservice.sender.list
     * @param string $messageId External message identifier received by the handler on message send
     * @param string $status New message status
     *
     * @throws BaseException
     * @throws TransportException
     * @link https://apidocs.bitrix24.com/api-reference/messageservice/messageservice-message-status-update.html
     */
    #[ApiEndpointMetadata(
        'messageservice.message.status.update',
        'https://apidocs.bitrix24.com/api-reference/messageservice/messageservice-message-status-update.html',
        'Update delivery status of a message sent via a message service provider'
    )]
    public function update(
        string $code,
        string $messageId,
        string $status
    ): MessageStatusUpdateResult {
        return new MessageStatusUpdateResult(
            $this->core->call('messageservice.message.status.update', [
                'CODE' => $code,
                'MESSAGE_ID' => $messageId,
                'STATUS' => $status,
            ])
        );
    }
}
