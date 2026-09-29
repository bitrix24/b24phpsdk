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

namespace Bitrix24\SDK\Services\MailService\Service;

use Bitrix24\SDK\Attributes\ApiEndpointMetadata;
use Bitrix24\SDK\Attributes\ApiServiceMetadata;
use Bitrix24\SDK\Core\Contracts\CoreInterface;
use Bitrix24\SDK\Core\Credentials\Scope;
use Bitrix24\SDK\Core\Exceptions\BaseException;
use Bitrix24\SDK\Core\Exceptions\TransportException;
use Bitrix24\SDK\Core\Result\AddedItemResult;
use Bitrix24\SDK\Core\Result\DeletedItemResult;
use Bitrix24\SDK\Core\Result\FieldsResult;
use Bitrix24\SDK\Core\Result\UpdatedItemResult;
use Bitrix24\SDK\Services\AbstractService;
use Bitrix24\SDK\Services\MailService\Result\MailServiceResult;
use Bitrix24\SDK\Services\MailService\Result\MailServicesResult;
use Psr\Log\LoggerInterface;

#[ApiServiceMetadata(new Scope(['mailservice']))]
class MailService extends AbstractService
{
    public function __construct(public Batch $batch, CoreInterface $core, LoggerInterface $logger)
    {
        parent::__construct($core, $logger);
    }

    /**
     * Add a new mail service.
     *
     * @link https://apidocs.bitrix24.com/api-reference/mailservice/mailservice-add.html
     *
     * @throws BaseException
     * @throws TransportException
     */
    #[ApiEndpointMetadata(
        'mailservice.add',
        'https://apidocs.bitrix24.com/api-reference/mailservice/mailservice-add.html',
        'Creates a new mail service for the current Bitrix24'
    )]
    public function add(
        string $name,
        string $active = 'Y',
        string $server = '',
        int $port = 993,
        string $encryption = 'Y',
        string $link = '',
        int $sort = 100
    ): AddedItemResult {
        $params = [
            'NAME' => $name,
            'ACTIVE' => $active,
            'SORT' => $sort,
        ];
        if ($server !== '') {
            $params['SERVER'] = $server;
        }

        if ($port !== 993) {
            $params['PORT'] = $port;
        }

        if ($link !== '') {
            $params['LINK'] = $link;
        }

        $params['ENCRYPTION'] = $encryption;

        return new AddedItemResult(
            $this->core->call('mailservice.add', $params)
        );
    }

    /**
     * Update an existing mail service.
     *
     * @link https://apidocs.bitrix24.com/api-reference/mailservice/mailservice-update.html
     *
     * @param array{
     *   NAME?: string,
     *   ACTIVE?: string,
     *   SERVER?: string,
     *   PORT?: int,
     *   ENCRYPTION?: string,
     *   LINK?: string,
     *   SORT?: int,
     * } $fields
     *
     * @throws BaseException
     * @throws TransportException
     */
    #[ApiEndpointMetadata(
        'mailservice.update',
        'https://apidocs.bitrix24.com/api-reference/mailservice/mailservice-update.html',
        'Updates an existing mail service parameters'
    )]
    public function update(int $id, array $fields): UpdatedItemResult
    {
        $params = $fields;
        $params['ID'] = $id;

        return new UpdatedItemResult(
            $this->core->call('mailservice.update', $params)
        );
    }

    /**
     * Get mail service by ID.
     *
     * @link https://apidocs.bitrix24.com/api-reference/mailservice/mailservice-get.html
     *
     * @throws BaseException
     * @throws TransportException
     */
    #[ApiEndpointMetadata(
        'mailservice.get',
        'https://apidocs.bitrix24.com/api-reference/mailservice/mailservice-get.html',
        'Returns mail service parameters by its identifier'
    )]
    public function get(int $id): MailServiceResult
    {
        return new MailServiceResult(
            $this->core->call('mailservice.get', ['ID' => $id])
        );
    }

    /**
     * Get list of active mail services.
     *
     * @link https://apidocs.bitrix24.com/api-reference/mailservice/mailservice-list.html
     *
     * @throws BaseException
     * @throws TransportException
     */
    #[ApiEndpointMetadata(
        'mailservice.list',
        'https://apidocs.bitrix24.com/api-reference/mailservice/mailservice-list.html',
        'Returns the list of active mail services'
    )]
    public function list(): MailServicesResult
    {
        return new MailServicesResult(
            $this->core->call('mailservice.list')
        );
    }

    /**
     * Delete a mail service by ID.
     *
     * @link https://apidocs.bitrix24.com/api-reference/mailservice/mailservice-delete.html
     *
     * @throws BaseException
     * @throws TransportException
     */
    #[ApiEndpointMetadata(
        'mailservice.delete',
        'https://apidocs.bitrix24.com/api-reference/mailservice/mailservice-delete.html',
        'Deletes a mail service by its identifier'
    )]
    public function delete(int $id): DeletedItemResult
    {
        return new DeletedItemResult(
            $this->core->call('mailservice.delete', ['ID' => $id])
        );
    }

    /**
     * Get the localized field labels of a mail service.
     *
     * @link https://apidocs.bitrix24.com/api-reference/mailservice/mailservice-fields.html
     *
     * @throws BaseException
     * @throws TransportException
     */
    #[ApiEndpointMetadata(
        'mailservice.fields',
        'https://apidocs.bitrix24.com/api-reference/mailservice/mailservice-fields.html',
        'Returns localized field labels of mail service'
    )]
    public function fields(): FieldsResult
    {
        return new FieldsResult($this->core->call('mailservice.fields'));
    }

    /**
     * Count active mail services.
     *
     * @throws BaseException
     * @throws TransportException
     */
    public function count(): int
    {
        return count($this->list()->getMailServices());
    }
}
