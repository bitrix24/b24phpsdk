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

namespace Bitrix24\SDK\Services\Mail\MailboxField\Service;

use Bitrix24\SDK\Attributes\ApiEndpointMetadata;
use Bitrix24\SDK\Attributes\ApiServiceMetadata;
use Bitrix24\SDK\Core\Contracts\ApiVersion;
use Bitrix24\SDK\Core\Credentials\Scope;
use Bitrix24\SDK\Core\Exceptions\BaseException;
use Bitrix24\SDK\Core\Exceptions\TransportException;
use Bitrix24\SDK\Services\AbstractService;
use Bitrix24\SDK\Services\Mail\MailboxField\Result\MailboxFieldResult;
use Bitrix24\SDK\Services\Mail\MailboxField\Result\MailboxFieldsResult;

#[ApiServiceMetadata(new Scope(['mail']))]
class MailboxField extends AbstractService
{
    /**
     * @param non-empty-string $name
     * @param string[]         $select
     *
     * @throws BaseException
     * @throws TransportException
     *
     * @link https://apidocs.bitrix24.com/api-reference/rest-v3/mail/mailbox/mail-mailbox-field-get.html
     */
    #[ApiEndpointMetadata(
        'mail.mailbox.field.get',
        'https://apidocs.bitrix24.com/api-reference/rest-v3/mail/mailbox/mail-mailbox-field-get.html',
        'Get metadata for a single mailbox field by name',
        ApiVersion::v3
    )]
    public function get(string $name, array $select = []): MailboxFieldResult
    {
        $this->guardNonEmptyString($name, 'field name must not be empty');

        $params = ['name' => $name];
        if ($select !== []) {
            $params['select'] = $select;
        }

        return new MailboxFieldResult(
            $this->core->call('mail.mailbox.field.get', $params, ApiVersion::v3)
        );
    }

    /**
     * @param string[] $select
     *
     * @throws BaseException
     * @throws TransportException
     *
     * @link https://apidocs.bitrix24.com/api-reference/rest-v3/mail/mailbox/mail-mailbox-field-list.html
     */
    #[ApiEndpointMetadata(
        'mail.mailbox.field.list',
        'https://apidocs.bitrix24.com/api-reference/rest-v3/mail/mailbox/mail-mailbox-field-list.html',
        'Get list of all available mailbox field descriptors',
        ApiVersion::v3
    )]
    public function list(array $select = []): MailboxFieldsResult
    {
        $params = $select !== [] ? ['select' => $select] : [];

        return new MailboxFieldsResult(
            $this->core->call('mail.mailbox.field.list', $params, ApiVersion::v3)
        );
    }
}
