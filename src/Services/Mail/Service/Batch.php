<?php

/**
 * This file is part of the bitrix24-php-sdk package.
 *
 * © Maksim Mesilov <mesilov.maxim@gmail.com>
 *
 * For the full copyright and license information, please view the MIT-LICENSE.txt
 * file that was distributed with this source code.
 */

declare(strict_types=1);

namespace Bitrix24\SDK\Services\Mail\Service;

use Bitrix24\SDK\Attributes\ApiBatchMethodMetadata;
use Bitrix24\SDK\Attributes\ApiBatchServiceMetadata;
use Bitrix24\SDK\Core\Contracts\BatchOperationsInterface;
use Bitrix24\SDK\Core\Credentials\Scope;
use Bitrix24\SDK\Core\Exceptions\BaseException;
use Bitrix24\SDK\Services\Mail\Result\MailboxItemResult;
use Bitrix24\SDK\Services\Mail\Result\MessageItemResult;
use Bitrix24\SDK\Services\Mail\Result\RecipientItemResult;
use Bitrix24\SDK\Services\Mail\Result\SendMessageItemResult;
use Generator;
use Psr\Log\LoggerInterface;

#[ApiBatchServiceMetadata(new Scope(['mail']))]
readonly class Batch
{
    public function __construct(
        protected BatchOperationsInterface $batch,
        protected LoggerInterface $log
    ) {
    }

    /**
     * @param array<string, mixed> $order
     * @param array<string, mixed> $filter
     * @param string[]             $select
     *
     * @return Generator<int, MailboxItemResult>
     * @throws BaseException
     */
    #[ApiBatchMethodMetadata(
        'mail.mailbox.list',
        'https://apidocs.bitrix24.com/api-reference/rest-v3/mail/mailbox/mail-mailbox-list.html',
        'Batch mailbox list'
    )]
    public function mailboxList(array $order = [], array $filter = [], array $select = [], ?int $limit = null): Generator
    {
        foreach ($this->batch->getTraversableList('mail.mailbox.list', $order, $filter, $select, $limit) as $key => $value) {
            yield $key => new MailboxItemResult($value);
        }
    }

    /**
     * @param array<string, mixed> $order
     * @param array<string, mixed> $filter
     * @param string[]             $select
     *
     * @return Generator<int, MessageItemResult>
     * @throws BaseException
     */
    #[ApiBatchMethodMetadata(
        'mail.message.list',
        'https://apidocs.bitrix24.com/api-reference/rest-v3/mail/message/mail-message-list.html',
        'Batch message list'
    )]
    public function messageList(array $order = [], array $filter = [], array $select = [], ?int $limit = null): Generator
    {
        foreach ($this->batch->getTraversableList('mail.message.list', $order, $filter, $select, $limit) as $key => $value) {
            yield $key => new MessageItemResult($value);
        }
    }

    /**
     * @param array<string, mixed> $order
     * @param array<string, mixed> $filter
     * @param string[]             $select
     *
     * @return Generator<int, RecipientItemResult>
     * @throws BaseException
     */
    #[ApiBatchMethodMetadata(
        'mail.recipient.listcontacts',
        'https://apidocs.bitrix24.com/api-reference/rest-v3/mail/recipient/mail-recipient-listcontacts.html',
        'Batch recipient contact list'
    )]
    public function recipientListContacts(array $order = [], array $filter = [], array $select = [], ?int $limit = null): Generator
    {
        foreach ($this->batch->getTraversableList('mail.recipient.listcontacts', $order, $filter, $select, $limit) as $key => $value) {
            yield $key => new RecipientItemResult($value);
        }
    }

    /**
     * @param array<string, mixed> $order
     * @param array<string, mixed> $filter
     * @param string[]             $select
     *
     * @return Generator<int, RecipientItemResult>
     * @throws BaseException
     */
    #[ApiBatchMethodMetadata(
        'mail.recipient.listemployees',
        'https://apidocs.bitrix24.com/api-reference/rest-v3/mail/recipient/mail-recipient-listemployees.html',
        'Batch recipient employee list'
    )]
    public function recipientListEmployees(array $order = [], array $filter = [], array $select = [], ?int $limit = null): Generator
    {
        foreach ($this->batch->getTraversableList('mail.recipient.listemployees', $order, $filter, $select, $limit) as $key => $value) {
            yield $key => new RecipientItemResult($value);
        }
    }

    /**
     * @param array<int, array<string, mixed>> $messages
     *
     * @return Generator<int, SendMessageItemResult>
     * @throws BaseException
     */
    #[ApiBatchMethodMetadata(
        'mail.message.send',
        'https://apidocs.bitrix24.com/api-reference/rest-v3/mail/message/mail-message-send.html',
        'Batch message send'
    )]
    public function messageSend(array $messages): Generator
    {
        foreach ($this->batch->addEntityItems('mail.message.send', $messages) as $key => $item) {
            yield $key => new SendMessageItemResult($item->getResult());
        }
    }
}
