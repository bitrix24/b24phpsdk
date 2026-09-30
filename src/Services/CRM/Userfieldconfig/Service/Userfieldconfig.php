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

namespace Bitrix24\SDK\Services\CRM\Userfieldconfig\Service;

use Bitrix24\SDK\Attributes\ApiEndpointMetadata;
use Bitrix24\SDK\Attributes\ApiServiceMetadata;
use Bitrix24\SDK\Core\Contracts\CoreInterface;
use Bitrix24\SDK\Core\Credentials\Scope;
use Bitrix24\SDK\Core\Exceptions\BaseException;
use Bitrix24\SDK\Core\Exceptions\TransportException;
use Bitrix24\SDK\Services\AbstractService;
use Bitrix24\SDK\Services\CRM\Userfieldconfig\Result\AddedUserfieldConfigItemResult;
use Bitrix24\SDK\Services\CRM\Userfieldconfig\Result\DeletedUserfieldConfigItemResult;
use Bitrix24\SDK\Services\CRM\Userfieldconfig\Result\UpdatedUserfieldConfigItemResult;
use Bitrix24\SDK\Services\CRM\Userfieldconfig\Result\UserfieldConfigResult;
use Bitrix24\SDK\Services\CRM\Userfieldconfig\Result\UserfieldConfigsResult;
use Bitrix24\SDK\Services\CRM\Userfieldconfig\Result\UserfieldConfigTypesResult;
use Psr\Log\LoggerInterface;

#[ApiServiceMetadata(new Scope(['crm', 'userfieldconfig']))]
class Userfieldconfig extends AbstractService
{
    public function __construct(public Batch $batch, CoreInterface $core, LoggerInterface $logger)
    {
        parent::__construct($core, $logger);
    }

    /**
     * Adds a new custom field for a CRM smart-process type, the new invoice or the sign document entity.
     *
     * @link https://apidocs.bitrix24.com/api-reference/crm/universal/userfieldconfig/userfieldconfig-add.html
     *
     * @param non-empty-string $moduleId
     * @param array $field
     *
     * @throws BaseException
     * @throws TransportException
     */
    #[ApiEndpointMetadata(
        'userfieldconfig.add',
        'https://apidocs.bitrix24.com/api-reference/crm/universal/userfieldconfig/userfieldconfig-add.html',
        'Adds a new custom field for a CRM smart-process type, the new invoice or the sign document entity.'
    )]
    public function add(string $moduleId, array $field): AddedUserfieldConfigItemResult
    {
        return new AddedUserfieldConfigItemResult(
            $this->core->call('userfieldconfig.add', [
                'moduleId' => $moduleId,
                'field' => $field,
            ])
        );
    }

    /**
     * Updates the settings of an existing custom field.
     *
     * @link https://apidocs.bitrix24.com/api-reference/crm/universal/userfieldconfig/userfieldconfig-update.html
     *
     * @param non-empty-string $moduleId
     * @param positive-int $id
     * @param array $field
     *
     * @throws BaseException
     * @throws TransportException
     */
    #[ApiEndpointMetadata(
        'userfieldconfig.update',
        'https://apidocs.bitrix24.com/api-reference/crm/universal/userfieldconfig/userfieldconfig-update.html',
        'Updates the settings of an existing custom field.'
    )]
    public function update(string $moduleId, int $id, array $field): UpdatedUserfieldConfigItemResult
    {
        return new UpdatedUserfieldConfigItemResult(
            $this->core->call('userfieldconfig.update', [
                'moduleId' => $moduleId,
                'id' => $id,
                'field' => $field,
            ])
        );
    }

    /**
     * Returns the settings of a custom field by its identifier.
     *
     * @link https://apidocs.bitrix24.com/api-reference/crm/universal/userfieldconfig/userfieldconfig-get.html
     *
     * @param non-empty-string $moduleId
     * @param positive-int $id
     *
     * @throws BaseException
     * @throws TransportException
     */
    #[ApiEndpointMetadata(
        'userfieldconfig.get',
        'https://apidocs.bitrix24.com/api-reference/crm/universal/userfieldconfig/userfieldconfig-get.html',
        'Returns the settings of a custom field by its identifier.'
    )]
    public function get(string $moduleId, int $id): UserfieldConfigResult
    {
        return new UserfieldConfigResult(
            $this->core->call('userfieldconfig.get', [
                'moduleId' => $moduleId,
                'id' => $id,
            ])
        );
    }

    /**
     * Returns a list of custom field settings matching the filter.
     *
     * @link https://apidocs.bitrix24.com/api-reference/crm/universal/userfieldconfig/userfieldconfig-list.html
     *
     * @param non-empty-string $moduleId
     * @param array $select defaults to `['*']` because the API returns only a minimal,
     *              largely useless field set (e.g. `{"1":"1","settings":null}`) when select is empty
     * @param array $order
     * @param array $filter
     * @param int $start
     *
     * @throws BaseException
     * @throws TransportException
     */
    #[ApiEndpointMetadata(
        'userfieldconfig.list',
        'https://apidocs.bitrix24.com/api-reference/crm/universal/userfieldconfig/userfieldconfig-list.html',
        'Returns a list of custom field settings matching the filter.'
    )]
    public function list(string $moduleId, array $select = ['*'], array $order = [], array $filter = [], int $start = 0): UserfieldConfigsResult
    {
        return new UserfieldConfigsResult(
            $this->core->call('userfieldconfig.list', [
                'moduleId' => $moduleId,
                'select' => $select,
                'order' => $order,
                'filter' => $filter,
                'start' => $start,
            ])
        );
    }

    /**
     * Deletes a custom field.
     *
     * @link https://apidocs.bitrix24.com/api-reference/crm/universal/userfieldconfig/userfieldconfig-delete.html
     *
     * @param non-empty-string $moduleId
     * @param positive-int $id
     *
     * @throws BaseException
     * @throws TransportException
     */
    #[ApiEndpointMetadata(
        'userfieldconfig.delete',
        'https://apidocs.bitrix24.com/api-reference/crm/universal/userfieldconfig/userfieldconfig-delete.html',
        'Deletes a custom field.'
    )]
    public function delete(string $moduleId, int $id): DeletedUserfieldConfigItemResult
    {
        return new DeletedUserfieldConfigItemResult(
            $this->core->call('userfieldconfig.delete', [
                'moduleId' => $moduleId,
                'id' => $id,
            ])
        );
    }

    /**
     * Returns the set of custom field types available for the specified module.
     *
     * @link https://apidocs.bitrix24.com/api-reference/crm/universal/userfieldconfig/userfieldconfig-get-types.html
     *
     * @param non-empty-string $moduleId
     *
     * @throws BaseException
     * @throws TransportException
     */
    #[ApiEndpointMetadata(
        'userfieldconfig.getTypes',
        'https://apidocs.bitrix24.com/api-reference/crm/universal/userfieldconfig/userfieldconfig-get-types.html',
        'Returns the set of custom field types available for the specified module.'
    )]
    public function getTypes(string $moduleId): UserfieldConfigTypesResult
    {
        return new UserfieldConfigTypesResult(
            $this->core->call('userfieldconfig.getTypes', [
                'moduleId' => $moduleId,
            ])
        );
    }
}
