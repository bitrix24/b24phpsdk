<?php

/**
 * This file is part of the bitrix24-php-sdk package.
 *
 * © Vadim Soluyanov <vadimsallee@gmail.com>
 *
 * For the full copyright and license information, please view the MIT-LICENSE.txt
 * file that was distributed with this source code.
 */

declare(strict_types=1);

namespace Bitrix24\SDK\Services\Task\Userfield\Service;

use Bitrix24\SDK\Attributes\ApiEndpointMetadata;
use Bitrix24\SDK\Attributes\ApiServiceMetadata;
use Bitrix24\SDK\Core\Contracts\CoreInterface;
use Bitrix24\SDK\Core\Credentials\Scope;
use Bitrix24\SDK\Core\Exceptions\BaseException;
use Bitrix24\SDK\Core\Exceptions\TransportException;
use Bitrix24\SDK\Core\Result\AddedItemResult;
use Bitrix24\SDK\Core\Result\DeletedItemResult;
use Bitrix24\SDK\Core\Result\UpdatedItemResult;
use Bitrix24\SDK\Services\AbstractService;
use Bitrix24\SDK\Services\Task\Userfield\Result\UserfieldResult;
use Bitrix24\SDK\Services\Task\Userfield\Result\UserfieldsResult;
use Bitrix24\SDK\Services\Task\Userfield\Result\UserfieldTypesResult;
use Bitrix24\SDK\Services\Task\Userfield\Result\UserfieldFieldsResult;
use Bitrix24\SDK\Services\Task\Userfield\Exceptions\UserfieldNameIsTooLongException;
use Bitrix24\SDK\Services\Task\Userfield\Service\UserfieldConstraints;
use Psr\Log\LoggerInterface;

#[ApiServiceMetadata(new Scope(['task']))]
class Userfield extends AbstractService
{
    public function __construct(private readonly UserfieldConstraints $userfieldConstraints, CoreInterface $core, LoggerInterface $logger)
    {
        parent::__construct($core, $logger);
    }

    /**
     * Returns list of user fields by filter.
     *
     * @param array{
     *   ID?: string,
     *   ENTITY_ID?: string,
     *   FIELD_NAME?: string,
     *   USER_TYPE_ID?: string,
     *   XML_ID?: string,
     *   SORT?: string,
     *   MULTIPLE?: string,
     *   MANDATORY?: string,
     *   SHOW_FILTER?: string,
     *   SHOW_IN_LIST?: string,
     *   EDIT_IN_LIST?: string,
     *   IS_SEARCHABLE?: string,
     *   EDIT_FORM_LABEL?: string,
     *   LIST_COLUMN_LABEL?: string,
     *   LIST_FILTER_LABEL?: string,
     *   ERROR_MESSAGE?: string,
     *   HELP_MESSAGE?: string,
     *   LIST?: string,
     *   SETTINGS?: string,
     *   } $order
     * @param array{
     *   ID?: string,
     *   ENTITY_ID?: string,
     *   FIELD_NAME?: string,
     *   USER_TYPE_ID?: string,
     *   XML_ID?: string,
     *   SORT?: string,
     *   MULTIPLE?: string,
     *   MANDATORY?: string,
     *   SHOW_FILTER?: string,
     *   SHOW_IN_LIST?: string,
     *   EDIT_IN_LIST?: string,
     *   IS_SEARCHABLE?: string,
     *   EDIT_FORM_LABEL?: string,
     *   LIST_COLUMN_LABEL?: string,
     *   LIST_FILTER_LABEL?: string,
     *   ERROR_MESSAGE?: string,
     *   HELP_MESSAGE?: string,
     *   LIST?: string,
     *   SETTINGS?: string,
     *   } $filter
     *
     * @throws BaseException
     * @throws TransportException
     * @link https://apidocs.bitrix24.com/api-reference/tasks/user-field/task-item-user-field-get-list.html
     */
    #[ApiEndpointMetadata(
        'task.item.userfield.list',
        'https://apidocs.bitrix24.com/api-reference/tasks/user-field/task-item-user-field-get-list.html',
        'Returns list of user task fields by filter.'
    )]
    public function getList(array $order, array $filter): UserfieldsResult
    {
        return new UserfieldsResult(
            $this->core->call(
                'task.item.userfield.getlist',
                [
                    'ORDER'  => $order,
                    'FILTER' => $filter,
                ]
            )
        );
    }

    /**
     * Created new user field for tasks.
     *
     * System limitation for field name - 20 characters.
     *
     * @param array{
     *   FIELD_NAME?: string,
     *   USER_TYPE_ID?: string,
     *   XML_ID?: string,
     *   SORT?: string,
     *   MULTIPLE?: string,
     *   MANDATORY?: string,
     *   SHOW_FILTER?: string,
     *   SHOW_IN_LIST?: string,
     *   EDIT_IN_LIST?: string,
     *   IS_SEARCHABLE?: string,
     *   EDIT_FORM_LABEL?: string|array,
     *   LIST_COLUMN_LABEL?: string|array,
     *   LIST_FILTER_LABEL?: string|array,
     *   ERROR_MESSAGE?: string,
     *   HELP_MESSAGE?: string,
     *   LIST?: string,
     *   SETTINGS?: array,
     *   } $userfieldItemFields
     *
     * @throws BaseException
     * @throws TransportException
     * @throws UserfieldNameIsTooLongException
     * @link https://apidocs.bitrix24.com/api-reference/tasks/user-field/task-item-user-field-add.html
     *
     */
    #[ApiEndpointMetadata(
        'task.item.userfield.add',
        'https://apidocs.bitrix24.com/api-reference/tasks/user-field/task-item-user-field-add.html',
        'Created new user field for tasks.'
    )]
    public function add(array $userfieldItemFields): AddedItemResult
    {
        $this->userfieldConstraints->validName($userfieldItemFields['FIELD_NAME']);

        return new AddedItemResult(
            $this->core->call(
                'task.item.userfield.add',
                [
                    'PARAMS' => $userfieldItemFields,
                ]
            )
        );
    }

    /**
     * Deleted userfield for tasks
     *
     *
     * @throws BaseException
     * @throws TransportException
     * @link  https://apidocs.bitrix24.com/api-reference/tasks/user-field/task-item-user-field-delete.html
     *
     */
    #[ApiEndpointMetadata(
        'task.item.userfield.delete',
        'https://apidocs.bitrix24.com/api-reference/tasks/user-field/task-item-user-field-delete.html',
        'Deleted userfield for tasks'
    )]
    public function delete(int $userfieldId): DeletedItemResult
    {
        return new DeletedItemResult(
            $this->core->call(
                'task.item.userfield.delete',
                [
                    'ID' => $userfieldId,
                ]
            )
        );
    }

    /**
     * Retrieves a field by identifier id.
     *
     *
     * @throws BaseException
     * @throws TransportException
     * @link  https://apidocs.bitrix24.com/api-reference/crm/task/user-field/crm-task-user-field-get.html
     */
    #[ApiEndpointMetadata(
        'task.item.userfield.get',
        'https://apidocs.bitrix24.com/api-reference/crm/task/user-field/crm-task-user-field-get.html',
        'Retrieves a field by identifier id.'
    )]
    public function get(int $userfieldItemId): UserfieldResult
    {
        return new UserfieldResult(
            $this->core->call(
                'task.item.userfield.get',
                [
                    'ID' => $userfieldItemId,
                ]
            )
        );
    }

    /**
     * Updates an existing user field for tasks.
     *
     *
     * @throws BaseException
     * @throws TransportException
     * @link https://apidocs.bitrix24.com/api-reference/tasks/user-field/task-item-user-field-update.html
     */
    #[ApiEndpointMetadata(
        'task.item.userfield.update',
        'https://apidocs.bitrix24.com/api-reference/tasks/user-field/task-item-user-field-update.html',
        'Updates an existing user field for tasks.'
    )]
    public function update(int $userfieldItemId, array $userfieldFieldsToUpdate): UpdatedItemResult
    {
        return new UpdatedItemResult(
            $this->core->call(
                'task.item.userfield.update',
                [
                    'ID'     => $userfieldItemId,
                    'DATA' => $userfieldFieldsToUpdate,
                ]
            )
        );
    }

    /**
     * Retrieves all available data types.
     *
     *
     * @throws BaseException
     * @throws TransportException
     * @link https://apidocs.bitrix24.com/api-reference/tasks/user-field/task-item-user-field-get-types.html
     */
    #[ApiEndpointMetadata(
        'task.item.userfield.gettypes',
        'https://apidocs.bitrix24.com/api-reference/tasks/user-field/task-item-user-field-get-types.html',
        'Retrieves all available data types.'
    )]
    public function getTypes(): UserfieldTypesResult
    {
        return new UserfieldTypesResult(
            $this->core->call(
                'task.item.userfield.gettypes',
                [
                ]
            )
        );
    }

    /**
     * Retrieves all available fields of the custom field.
     *
     *
     * @throws BaseException
     * @throws TransportException
     * @link https://apidocs.bitrix24.com/api-reference/tasks/user-field/task-item-user-field-get-fields.html
     */
    #[ApiEndpointMetadata(
        'task.item.userfield.getfields',
        'https://apidocs.bitrix24.com/api-reference/tasks/user-field/task-item-user-field-get-fields.html',
        'Retrieves all available fields of the custom field.'
    )]
    public function getFields(): UserfieldFieldsResult
    {
        return new UserfieldFieldsResult(
            $this->core->call(
                'task.item.userfield.getfields',
                [
                ]
            )
        );
    }
}
