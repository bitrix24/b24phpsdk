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
use Bitrix24\SDK\Services\Mail\Result\MailboxResult;
use Bitrix24\SDK\Services\Mail\Result\MailboxesResult;
use Bitrix24\SDK\Services\Mail\Result\SendersResult;
use Psr\Log\LoggerInterface;

#[ApiServiceMetadata(new Scope(['mail']))]
class Mailbox extends AbstractService
{
    public function __construct(public Batch $batch, CoreInterface $core, LoggerInterface $logger)
    {
        parent::__construct($core, $logger);
    }

    /**
     * @param string[] $select
     *
     * @throws BaseException
     * @throws TransportException
     *
     * @link https://apidocs.bitrix24.com/api-reference/rest-v3/mail/mailbox/mail-mailbox-get.html
     */
    #[ApiEndpointMetadata(
        'mail.mailbox.get',
        'https://apidocs.bitrix24.com/api-reference/rest-v3/mail/mailbox/mail-mailbox-get.html',
        'Get mailbox by identifier',
        ApiVersion::v3
    )]
    public function get(int $id, array $select = []): MailboxResult
    {
        $params = ['id' => $id];
        if ($select !== []) {
            $params['select'] = $select;
        }

        return new MailboxResult($this->core->call('mail.mailbox.get', $params, ApiVersion::v3));
    }

    /**
     * @param array<string, mixed> $pagination
     * @param string[]             $select
     * @param array<string, mixed> $filter
     * @param array<string, mixed> $order
     *
     * @throws BaseException
     * @throws TransportException
     *
     * @link https://apidocs.bitrix24.com/api-reference/rest-v3/mail/mailbox/mail-mailbox-list.html
     */
    #[ApiEndpointMetadata(
        'mail.mailbox.list',
        'https://apidocs.bitrix24.com/api-reference/rest-v3/mail/mailbox/mail-mailbox-list.html',
        'Get mailbox list',
        ApiVersion::v3
    )]
    public function list(
        ?string $name = null,
        ?string $email = null,
        array $pagination = [],
        array $select = [],
        array $filter = [],
        array $order = []
    ): MailboxesResult {
        $params = [];
        if ($name !== null) {
            $params['name'] = $name;
        }
        if ($email !== null) {
            $params['email'] = $email;
        }
        if ($pagination !== []) {
            $params['pagination'] = $pagination;
        }
        if ($select !== []) {
            $params['select'] = $select;
        }
        if ($filter !== []) {
            $params['filter'] = $filter;
        }
        if ($order !== []) {
            $params['order'] = $order;
        }

        return new MailboxesResult($this->core->call('mail.mailbox.list', $params, ApiVersion::v3));
    }

    /**
     * @param array<string, mixed> $pagination
     *
     * @throws BaseException
     * @throws TransportException
     *
     * @link https://apidocs.bitrix24.com/api-reference/rest-v3/mail/mailbox/mail-mailbox-senders.html
     */
    #[ApiEndpointMetadata(
        'mail.mailbox.senders',
        'https://apidocs.bitrix24.com/api-reference/rest-v3/mail/mailbox/mail-mailbox-senders.html',
        'Get available mailbox senders',
        ApiVersion::v3
    )]
    public function senders(array $pagination = []): SendersResult
    {
        $params = $pagination !== [] ? ['pagination' => $pagination] : [];

        return new SendersResult($this->core->call('mail.mailbox.senders', $params, ApiVersion::v3));
    }
}
