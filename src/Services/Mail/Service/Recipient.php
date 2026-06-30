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
use Bitrix24\SDK\Services\Mail\Result\RecipientsResult;
use Psr\Log\LoggerInterface;

#[ApiServiceMetadata(new Scope(['mail']))]
class Recipient extends AbstractService
{
    public function __construct(public Batch $batch, CoreInterface $core, LoggerInterface $logger)
    {
        parent::__construct($core, $logger);
    }

    /**
     * @param array<string, mixed> $pagination
     *
     * @throws BaseException
     * @throws TransportException
     *
     * @link https://apidocs.bitrix24.com/api-reference/rest-v3/mail/recipient/mail-recipient-listcontacts.html
     */
    #[ApiEndpointMetadata(
        'mail.recipient.listcontacts',
        'https://apidocs.bitrix24.com/api-reference/rest-v3/mail/recipient/mail-recipient-listcontacts.html',
        'Search contacts in the current user address book',
        ApiVersion::v3
    )]
    public function listContacts(?string $query = null, array $pagination = []): RecipientsResult
    {
        $params = [];
        if ($query !== null) {
            $params['query'] = $query;
        }
        if ($pagination !== []) {
            $params['pagination'] = $pagination;
        }

        return new RecipientsResult($this->core->call('mail.recipient.listcontacts', $params, ApiVersion::v3));
    }

    /**
     * @param array<string, mixed> $pagination
     *
     * @throws BaseException
     * @throws TransportException
     *
     * @link https://apidocs.bitrix24.com/api-reference/rest-v3/mail/recipient/mail-recipient-listemployees.html
     */
    #[ApiEndpointMetadata(
        'mail.recipient.listemployees',
        'https://apidocs.bitrix24.com/api-reference/rest-v3/mail/recipient/mail-recipient-listemployees.html',
        'Search employees by name or email',
        ApiVersion::v3
    )]
    public function listEmployees(string $query, array $pagination = []): RecipientsResult
    {
        $params = ['query' => $query];
        if ($pagination !== []) {
            $params['pagination'] = $pagination;
        }

        return new RecipientsResult($this->core->call('mail.recipient.listemployees', $params, ApiVersion::v3));
    }
}
