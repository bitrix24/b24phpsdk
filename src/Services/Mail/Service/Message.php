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

use Bitrix24\SDK\Attributes\ApiEndpointMetadata;
use Bitrix24\SDK\Attributes\ApiServiceMetadata;
use Bitrix24\SDK\Core\Contracts\ApiVersion;
use Bitrix24\SDK\Core\Contracts\CoreInterface;
use Bitrix24\SDK\Core\Credentials\Scope;
use Bitrix24\SDK\Core\Exceptions\BaseException;
use Bitrix24\SDK\Core\Exceptions\TransportException;
use Bitrix24\SDK\Services\AbstractService;
use Bitrix24\SDK\Services\Mail\Result\BooleanResult;
use Bitrix24\SDK\Services\Mail\Result\CreateCalendarEventResult;
use Bitrix24\SDK\Services\Mail\Result\CreateChatResult;
use Bitrix24\SDK\Services\Mail\Result\CreateFeedPostResult;
use Bitrix24\SDK\Services\Mail\Result\CreateTaskResult;
use Bitrix24\SDK\Services\Mail\Result\MessageResult;
use Bitrix24\SDK\Services\Mail\Result\MessagesResult;
use Bitrix24\SDK\Services\Mail\Result\MessageThreadResult;
use Bitrix24\SDK\Services\Mail\Result\MoveToFolderResult;
use Bitrix24\SDK\Services\Mail\Result\SendMessageResult;
use Carbon\CarbonImmutable;
use Psr\Log\LoggerInterface;

#[ApiServiceMetadata(new Scope(['mail']))]
class Message extends AbstractService
{
    public function __construct(public Batch $batch, CoreInterface $core, LoggerInterface $logger)
    {
        parent::__construct($core, $logger);
    }

    /**
     * @throws BaseException
     * @throws TransportException
     *
     * @link https://apidocs.bitrix24.com/api-reference/rest-v3/mail/message/mail-message-createcalendarevent.html
     */
    #[ApiEndpointMetadata(
        'mail.message.createcalendarevent',
        'https://apidocs.bitrix24.com/api-reference/rest-v3/mail/message/mail-message-createcalendarevent.html',
        'Create calendar event from message',
        ApiVersion::v3
    )]
    public function createCalendarEvent(
        int $messageId,
        CarbonImmutable $dateFrom,
        CarbonImmutable $dateTo,
        ?string $name = null,
        ?string $description = null
    ): CreateCalendarEventResult {
        $params = [
            'messageId' => $messageId,
            'dateFrom' => $dateFrom->format('Y-m-d H:i:s'),
            'dateTo' => $dateTo->format('Y-m-d H:i:s'),
        ];
        if ($name !== null) {
            $params['name'] = $name;
        }
        if ($description !== null) {
            $params['description'] = $description;
        }

        return new CreateCalendarEventResult(
            $this->core->call('mail.message.createcalendarevent', $params, ApiVersion::v3)
        );
    }

    #[ApiEndpointMetadata(
        'mail.message.createchat',
        'https://apidocs.bitrix24.com/api-reference/rest-v3/mail/message/mail-message-createchat.html',
        'Create chat from message',
        ApiVersion::v3
    )]
    public function createChat(int $messageId): CreateChatResult
    {
        return new CreateChatResult(
            $this->core->call('mail.message.createchat', ['messageId' => $messageId], ApiVersion::v3)
        );
    }

    #[ApiEndpointMetadata(
        'mail.message.createcrmactivity',
        'https://apidocs.bitrix24.com/api-reference/rest-v3/mail/message/mail-message-createcrmactivity.html',
        'Create CRM activity from message',
        ApiVersion::v3
    )]
    public function createCrmActivity(int $messageId): BooleanResult
    {
        return new BooleanResult(
            $this->core->call('mail.message.createcrmactivity', ['messageId' => $messageId], ApiVersion::v3)
        );
    }

    #[ApiEndpointMetadata(
        'mail.message.createfeedpost',
        'https://apidocs.bitrix24.com/api-reference/rest-v3/mail/message/mail-message-createfeedpost.html',
        'Create feed post from message',
        ApiVersion::v3
    )]
    public function createFeedPost(int $messageId, ?string $title = null): CreateFeedPostResult
    {
        $params = ['messageId' => $messageId];
        if ($title !== null) {
            $params['title'] = $title;
        }

        return new CreateFeedPostResult(
            $this->core->call('mail.message.createfeedpost', $params, ApiVersion::v3)
        );
    }

    #[ApiEndpointMetadata(
        'mail.message.createtask',
        'https://apidocs.bitrix24.com/api-reference/rest-v3/mail/message/mail-message-createtask.html',
        'Create task from message',
        ApiVersion::v3
    )]
    public function createTask(
        int $messageId,
        ?string $title = null,
        ?int $responsibleId = null,
        ?string $description = null
    ): CreateTaskResult {
        $params = ['messageId' => $messageId];
        if ($title !== null) {
            $params['title'] = $title;
        }
        if ($responsibleId !== null) {
            $params['responsibleId'] = $responsibleId;
        }
        if ($description !== null) {
            $params['description'] = $description;
        }

        return new CreateTaskResult($this->core->call('mail.message.createtask', $params, ApiVersion::v3));
    }

    /**
     * @param string[] $to
     * @param string[] $cc
     * @param string[] $bcc
     */
    #[ApiEndpointMetadata(
        'mail.message.forward',
        'https://apidocs.bitrix24.com/api-reference/rest-v3/mail/message/mail-message-forward.html',
        'Forward message',
        ApiVersion::v3
    )]
    public function forward(
        int $forwardMessageId,
        string $from,
        array $to,
        string $subject,
        string $body,
        array $cc = [],
        array $bcc = []
    ): SendMessageResult {
        return new SendMessageResult(
            $this->core->call('mail.message.forward', $this->buildSendPayload([
                'forwardMessageId' => $forwardMessageId,
                'from' => $from,
                'to' => $to,
                'subject' => $subject,
                'body' => $body,
            ], $cc, $bcc), ApiVersion::v3)
        );
    }

    /**
     * @param string[] $select
     */
    #[ApiEndpointMetadata(
        'mail.message.get',
        'https://apidocs.bitrix24.com/api-reference/rest-v3/mail/message/mail-message-get.html',
        'Get message by identifier',
        ApiVersion::v3
    )]
    public function get(int $id, array $select = []): MessageResult
    {
        $params = ['id' => $id];
        if ($select !== []) {
            $params['select'] = $select;
        }

        return new MessageResult($this->core->call('mail.message.get', $params, ApiVersion::v3));
    }

    /**
     * @param array<string, mixed> $pagination
     */
    #[ApiEndpointMetadata(
        'mail.message.list',
        'https://apidocs.bitrix24.com/api-reference/rest-v3/mail/message/mail-message-list.html',
        'Get message list',
        ApiVersion::v3
    )]
    public function list(
        int $mailboxId,
        ?string $searchQuery = null,
        ?CarbonImmutable $dateFrom = null,
        ?CarbonImmutable $dateTo = null,
        ?bool $isSeen = null,
        ?bool $hasAttachments = null,
        ?string $folder = null,
        array $pagination = []
    ): MessagesResult {
        $params = ['mailboxId' => $mailboxId];
        if ($searchQuery !== null) {
            $params['searchQuery'] = $searchQuery;
        }
        if ($dateFrom instanceof CarbonImmutable) {
            $params['dateFrom'] = $dateFrom->format(CarbonImmutable::ATOM);
        }
        if ($dateTo instanceof CarbonImmutable) {
            $params['dateTo'] = $dateTo->format(CarbonImmutable::ATOM);
        }
        if ($isSeen !== null) {
            $params['isSeen'] = $isSeen;
        }
        if ($hasAttachments !== null) {
            $params['hasAttachments'] = $hasAttachments;
        }
        if ($folder !== null) {
            $params['folder'] = $folder;
        }
        if ($pagination !== []) {
            $params['pagination'] = $pagination;
        }

        return new MessagesResult($this->core->call('mail.message.list', $params, ApiVersion::v3));
    }

    /**
     * @param int[] $messageIds
     */
    #[ApiEndpointMetadata(
        'mail.message.movetofolder',
        'https://apidocs.bitrix24.com/api-reference/rest-v3/mail/message/mail-message-movetofolder.html',
        'Move messages to folder',
        ApiVersion::v3
    )]
    public function moveToFolder(array $messageIds, string $action, ?string $folder = null): MoveToFolderResult
    {
        $params = [
            'messageIds' => $messageIds,
            'action' => $action,
        ];
        if ($folder !== null) {
            $params['folder'] = $folder;
        }

        return new MoveToFolderResult($this->core->call('mail.message.movetofolder', $params, ApiVersion::v3));
    }

    #[ApiEndpointMetadata(
        'mail.message.removecrmactivity',
        'https://apidocs.bitrix24.com/api-reference/rest-v3/mail/message/mail-message-removecrmactivity.html',
        'Remove CRM activity from message',
        ApiVersion::v3
    )]
    public function removeCrmActivity(int $messageId): BooleanResult
    {
        return new BooleanResult(
            $this->core->call('mail.message.removecrmactivity', ['messageId' => $messageId], ApiVersion::v3)
        );
    }

    /**
     * @param string[] $to
     * @param string[] $cc
     * @param string[] $bcc
     */
    #[ApiEndpointMetadata(
        'mail.message.reply',
        'https://apidocs.bitrix24.com/api-reference/rest-v3/mail/message/mail-message-reply.html',
        'Reply to message',
        ApiVersion::v3
    )]
    public function reply(
        int $replyToMessageId,
        string $from,
        array $to,
        string $subject,
        string $body,
        array $cc = [],
        array $bcc = []
    ): SendMessageResult {
        return new SendMessageResult(
            $this->core->call('mail.message.reply', $this->buildSendPayload([
                'replyToMessageId' => $replyToMessageId,
                'from' => $from,
                'to' => $to,
                'subject' => $subject,
                'body' => $body,
            ], $cc, $bcc), ApiVersion::v3)
        );
    }

    /**
     * @param string[] $to
     * @param string[] $cc
     * @param string[] $bcc
     */
    #[ApiEndpointMetadata(
        'mail.message.send',
        'https://apidocs.bitrix24.com/api-reference/rest-v3/mail/message/mail-message-send.html',
        'Send message',
        ApiVersion::v3
    )]
    public function send(
        string $from,
        array $to,
        string $subject,
        string $body,
        array $cc = [],
        array $bcc = []
    ): SendMessageResult {
        return new SendMessageResult(
            $this->core->call('mail.message.send', $this->buildSendPayload([
                'from' => $from,
                'to' => $to,
                'subject' => $subject,
                'body' => $body,
            ], $cc, $bcc), ApiVersion::v3)
        );
    }

    #[ApiEndpointMetadata(
        'mail.message.thread',
        'https://apidocs.bitrix24.com/api-reference/rest-v3/mail/message/mail-message-thread.html',
        'Get message thread',
        ApiVersion::v3
    )]
    public function thread(int $id, ?int $limit = null): MessageThreadResult
    {
        $params = ['id' => $id];
        if ($limit !== null) {
            $params['limit'] = $limit;
        }

        return new MessageThreadResult($this->core->call('mail.message.thread', $params, ApiVersion::v3));
    }

    /**
     * @param array<string, mixed> $payload
     * @param string[]             $cc
     * @param string[]             $bcc
     *
     * @return array<string, mixed>
     */
    private function buildSendPayload(array $payload, array $cc, array $bcc): array
    {
        if ($cc !== []) {
            $payload['cc'] = $cc;
        }
        if ($bcc !== []) {
            $payload['bcc'] = $bcc;
        }

        return $payload;
    }
}
