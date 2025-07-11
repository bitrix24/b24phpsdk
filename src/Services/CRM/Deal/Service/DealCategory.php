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

namespace Bitrix24\SDK\Services\CRM\Deal\Service;

use Bitrix24\SDK\Attributes\ApiEndpointMetadata;
use Bitrix24\SDK\Attributes\ApiServiceMetadata;
use Bitrix24\SDK\Core\Credentials\Scope;
use Bitrix24\SDK\Core\Exceptions\BaseException;
use Bitrix24\SDK\Core\Exceptions\TransportException;
use Bitrix24\SDK\Core\Result\AddedItemResult;
use Bitrix24\SDK\Core\Result\DeletedItemResult;
use Bitrix24\SDK\Core\Result\FieldsResult;
use Bitrix24\SDK\Core\Result\UpdatedItemResult;
use Bitrix24\SDK\Services\AbstractService;
use Bitrix24\SDK\Services\CRM\Deal\Result\DealCategoriesResult;
use Bitrix24\SDK\Services\CRM\Deal\Result\DealCategoryResult;
use Bitrix24\SDK\Services\CRM\Deal\Result\DealCategoryStatusResult;

#[ApiServiceMetadata(new Scope(['crm']))]
class DealCategory extends AbstractService
{
    /**
     * Creates a new deal category.
     *
     * @link https://training.bitrix24.com/rest_help/crm/category/crm_dealcategory_add.php
     *
     * @param array{
     *   ID?: int,
     *   CREATED_DATE?: string,
     *   NAME?: string,
     *   IS_LOCKED?: string,
     *   SORT?: int,
     *   } $fields
     *
     * @throws BaseException
     * @throws TransportException
     */
    #[ApiEndpointMetadata(
        'crm.dealcategory.add',
        'https://training.bitrix24.com/rest_help/crm/category/crm_dealcategory_add.php',
        'Add new deal category'
    )]
    public function add(array $fields): AddedItemResult
    {
        return new AddedItemResult(
            $this->core->call(
                'crm.dealcategory.add',
                [
                    'fields' => $fields,
                ]
            )
        );
    }

    /**
     * Deletes a deal category.
     *
     * @link https://training.bitrix24.com/rest_help/crm/category/crm_dealcategory_delete.php
     *
     *
     * @throws BaseException
     * @throws TransportException
     */
    #[ApiEndpointMetadata(
        'crm.dealcategory.delete',
        'https://training.bitrix24.com/rest_help/crm/category/crm_dealcategory_delete.php',
        'Delete deal category'
    )]
    public function delete(int $categoryId): DeletedItemResult
    {
        return new DeletedItemResult(
            $this->core->call(
                'crm.dealcategory.delete',
                [
                    'id' => $categoryId,
                ]
            )
        );
    }

    /**
     * Returns field description for deal categories.
     *
     * @link https://training.bitrix24.com/rest_help/crm/category/crm_dealcategory_fields.php
     *
     * @throws BaseException
     * @throws TransportException
     */
    #[ApiEndpointMetadata(
        'crm.dealcategory.fields',
        'https://training.bitrix24.com/rest_help/crm/category/crm_dealcategory_fields.php',
        'Returns field description for deal categories'
    )]
    public function fields(): FieldsResult
    {
        return new FieldsResult($this->core->call('crm.dealcategory.fields'));
    }

    /**
     * The method reads settings for general deal category
     *
     * @link https://training.bitrix24.com/rest_help/crm/category/crm_dealcategory_default_get.php
     * @throws BaseException
     * @throws TransportException
     */
    #[ApiEndpointMetadata(
        'crm.dealcategory.default.get',
        'https://training.bitrix24.com/rest_help/crm/category/crm_dealcategory_default_get.php',
        'he method reads settings for general deal category'
    )]
    public function getDefaultCategorySettings(): DealCategoryResult
    {
        return new DealCategoryResult($this->core->call('crm.dealcategory.default.get'));
    }

    /**
     * The method writes settings for general deal category.
     *
     * @link https://training.bitrix24.com/rest_help/crm/category/crm_dealcategory_default_set.php
     *
     * @param array{
     *      NAME?: string,
     *      } $parameters
     *
     * @throws BaseException
     * @throws TransportException
     */
    #[ApiEndpointMetadata(
        'crm.dealcategory.default.set',
        'https://training.bitrix24.com/rest_help/crm/category/crm_dealcategory_default_set.php',
        'The method writes settings for general deal category.'
    )]
    public function setDefaultCategorySettings(array $parameters): UpdatedItemResult
    {
        return new UpdatedItemResult($this->core->call('crm.dealcategory.default.set', $parameters));
    }


    /**
     * Returns deal category by the ID
     *
     * @link https://training.bitrix24.com/rest_help/crm/category/crm_dealcategory_get.php
     *
     *
     * @throws BaseException
     * @throws TransportException
     */
    #[ApiEndpointMetadata(
        'crm.dealcategory.get',
        'https://training.bitrix24.com/rest_help/crm/category/crm_dealcategory_get.php',
        'Returns deal category by the ID'
    )]
    public function get(int $categoryId): DealCategoryResult
    {
        return new DealCategoryResult(
            $this->core->call(
                'crm.dealcategory.get',
                [
                    'id' => $categoryId,
                ]
            )
        );
    }

    /**
     * Returns a list of deal categories by the filter. Is the implementation of list method for deal categories.
     *
     * @link https://training.bitrix24.com/rest_help/crm/category/crm_dealcategory_list.php
     *
     *
     * @throws BaseException
     * @throws TransportException
     */
    #[ApiEndpointMetadata(
        'crm.dealcategory.list',
        'https://training.bitrix24.com/rest_help/crm/category/crm_dealcategory_list.php',
        'Returns a list of deal categories by the filter.'
    )]
    public function list(array $order, array $filter, array $select, int $start): DealCategoriesResult
    {
        return new DealCategoriesResult(
            $this->core->call(
                'crm.dealcategory.list',
                [
                    'order' => $order,
                    'filter' => $filter,
                    'select' => $select,
                    'start' => $start,
                ]
            )
        );
    }

    /**
     * Returns directory type ID for storage deal categories by the ID.
     *
     * @link https://training.bitrix24.com/rest_help/crm/category/crm_dealcategory_status.php
     *
     *
     * @throws BaseException
     * @throws TransportException
     */
    #[ApiEndpointMetadata(
        'crm.dealcategory.list',
        'https://training.bitrix24.com/rest_help/crm/category/crm_dealcategory_status.php',
        'Returns directory type ID for storage deal categories by the ID.'
    )]
    public function getStatus(int $categoryId): DealCategoryStatusResult
    {
        return new DealCategoryStatusResult(
            $this->core->call(
                'crm.dealcategory.status',
                [
                    'id' => $categoryId,
                ]
            )
        );
    }

    /**
     * Updates an existing category.
     *
     * @link https://training.bitrix24.com/rest_help/crm/category/crm_dealcategory_update.php
     *
     * @param array{
     *   ID?: int,
     *   CREATED_DATE?: string,
     *   NAME?: string,
     *   IS_LOCKED?: string,
     *   SORT?: int,
     *   } $fields
     *
     * @throws BaseException
     * @throws TransportException
     */
    #[ApiEndpointMetadata(
        'crm.dealcategory.update',
        'https://training.bitrix24.com/rest_help/crm/category/crm_dealcategory_update.php',
        'Updates an existing category.'
    )]
    public function update(int $categoryId, array $fields): UpdatedItemResult
    {
        return new UpdatedItemResult(
            $this->core->call(
                'crm.dealcategory.update',
                [
                    'id' => $categoryId,
                    'fields' => $fields,
                ]
            )
        );
    }
}
