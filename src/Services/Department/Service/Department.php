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

namespace Bitrix24\SDK\Services\Department\Service;

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
use Bitrix24\SDK\Services\Department\Result\DepartmentsResult;
use Psr\Log\LoggerInterface;

#[ApiServiceMetadata(new Scope(['department']))]
class Department extends AbstractService
{
    /**
     * Department constructor.
     */
    public function __construct(public Batch $batch, CoreInterface $core, LoggerInterface $logger)
    {
        parent::__construct($core, $logger);
    }

    /**
     * add new department
     *
     * @link https://apidocs.bitrix24.com/api-reference/departments/department-add.html
     *
     * @throws BaseException
     * @throws TransportException
     */
    #[ApiEndpointMetadata(
        'department.add',
        'https://apidocs.bitrix24.com/api-reference/departments/department-add.html',
        'Method adds new department'
    )]
    public function add(string $name, int $parent, int $sort = 100, int $head = 0): AddedItemResult
    {
        return new AddedItemResult(
            $this->core->call(
                'department.add',
                [
                    'NAME' => $name,
                    'SORT' => $sort,
                    'PARENT' => $parent,
                    'HEAD' => $head,
                ]
            )
        );
    }

    /**
     * Deletes a department.
     *
     * @link https://apidocs.bitrix24.com/api-reference/departments/department-delete.html
     *
     *
     * @throws BaseException
     * @throws TransportException
     */
    #[ApiEndpointMetadata(
        'department.delete',
        'https://apidocs.bitrix24.com/api-reference/departments/department-delete.html',
        'Deletes a department.'
    )]
    public function delete(int $id): DeletedItemResult
    {
        return new DeletedItemResult(
            $this->core->call(
                'department.delete',
                [
                    'ID' => $id,
                ]
            )
        );
    }

    /**
     * Get the department fields reference.
     *
     * @link https://apidocs.bitrix24.com/api-reference/departments/index.html
     *
     * @throws BaseException
     * @throws TransportException
     */
    #[ApiEndpointMetadata(
        'department.fields',
        'https://apidocs.bitrix24.com/api-reference/departments/index.html',
        'Get the department fields reference.'
    )]
    public function fields(): FieldsResult
    {
        return new FieldsResult($this->core->call('department.fields'));
    }

    /**
     * Retrieve a list of departments.
     *
     * @link https://apidocs.bitrix24.com/api-reference/departments/department-get.html
     *
     * @param array{
     *   ID?: int,
     *   NAME?: string,
     *   SORT?: int,
     *   PARENT?: int,
     *   UF_HEAD?: int,
     * } $filter
     *
     * @throws BaseException
     * @throws TransportException
     */
    #[ApiEndpointMetadata(
        'department.get',
        'https://apidocs.bitrix24.com/api-reference/departments/department-get.html',
        'Retrieve a list of departments.'
    )]
    public function get(array $filter = [], string $sort = 'ID', string $order = 'ASC', $start = 0): DepartmentsResult
    {
        $params = $filter;
        $params['SORT'] = $sort;
        $params['ORDER'] = $order;
        $params['START'] = $start;
        return new DepartmentsResult($this->core->call('department.get', $params));
    }

    /**
     * Updates the specified (existing) department.
     *
     * @link https://apidocs.bitrix24.com/api-reference/departments/department-update.html
     *
     * @param array{
     *   NAME?: string,
     *   SORT?: int,
     *   PARENT?: int,
     *   UF_HEAD?: int,
     *   } $fields
     *
     * @throws BaseException
     * @throws TransportException
     */
    #[ApiEndpointMetadata(
        'department.update',
        'https://apidocs.bitrix24.com/api-reference/departments/department-update.html',
        'Updates the specified (existing) department.'
    )]
    public function update(int $id, array $fields): UpdatedItemResult
    {
        $params = $fields;
        $params['ID'] = $id;
        return new UpdatedItemResult(
            $this->core->call(
                'department.update',
                $params
            )
        );
    }

    /**
     * Count departments by filter
     *
     * @param array{
     *   ID?: int,
     *   NAME?: string,
     *   SORT?: int,
     *   PARENT?: int,
     *   UF_HEAD?: int,
     *   } $filter
     *
     * @throws \Bitrix24\SDK\Core\Exceptions\BaseException
     * @throws \Bitrix24\SDK\Core\Exceptions\TransportException
     */
    public function countByFilter(array $filter = []): int
    {
        return $this->get($filter, 'ID', 'ASC', 1)->getCoreResponse()->getResponseData()->getPagination()->getTotal();
    }
}
