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

namespace Bitrix24\SDK\Services\Documentgenerator\Role\Service;

use Bitrix24\SDK\Attributes\ApiEndpointMetadata;
use Bitrix24\SDK\Attributes\ApiServiceMetadata;
use Bitrix24\SDK\Core\Contracts\CoreInterface;
use Bitrix24\SDK\Core\Credentials\Scope;
use Bitrix24\SDK\Core\Exceptions\BaseException;
use Bitrix24\SDK\Core\Exceptions\TransportException;
use Bitrix24\SDK\Services\AbstractService;
use Bitrix24\SDK\Services\Documentgenerator\Role\Result\AddedRoleResult;
use Bitrix24\SDK\Services\Documentgenerator\Role\Result\DeletedRoleResult;
use Bitrix24\SDK\Services\Documentgenerator\Role\Result\FillAccessesResult;
use Bitrix24\SDK\Services\Documentgenerator\Role\Result\RoleResult;
use Bitrix24\SDK\Services\Documentgenerator\Role\Result\RolesResult;
use Bitrix24\SDK\Services\Documentgenerator\Role\Result\UpdatedRoleResult;
use Psr\Log\LoggerInterface;

#[ApiServiceMetadata(new Scope(['documentgenerator']))]
class Role extends AbstractService
{
    /**
     * Role constructor
     */
    public function __construct(public Batch $batch, CoreInterface $core, LoggerInterface $logger)
    {
        parent::__construct($core, $logger);
    }

    /**
     * Creates a new role
     *
     * @link https://apidocs.bitrix24.com/api-reference/document-generator/role/document-generator-role-add.html
     *
     * @param array{
     *   name: string,
     *   code?: string,
     *   permissions?: array
     * } $fields
     *
     * @throws BaseException
     * @throws TransportException
     */
    #[ApiEndpointMetadata(
        'documentgenerator.role.add',
        'https://apidocs.bitrix24.com/api-reference/document-generator/role/document-generator-role-add.html',
        'Creates a new role'
    )]
    public function add(array $fields): AddedRoleResult
    {
        return new AddedRoleResult(
            $this->core->call(
                'documentgenerator.role.add',
                [
                    'fields' => $fields,
                ]
            )
        );
    }

    /**
     * Updates an existing role with new values
     *
     * @link https://apidocs.bitrix24.com/api-reference/document-generator/role/document-generator-role-update.html
     *
     * @param array{
     *   name?: string,
     *   code?: string,
     *   permissions?: array
     * } $fields
     *
     * @throws BaseException
     * @throws TransportException
     */
    #[ApiEndpointMetadata(
        'documentgenerator.role.update',
        'https://apidocs.bitrix24.com/api-reference/document-generator/role/document-generator-role-update.html',
        'Updates an existing role with new values'
    )]
    public function update(int $id, array $fields): UpdatedRoleResult
    {
        return new UpdatedRoleResult(
            $this->core->call(
                'documentgenerator.role.update',
                [
                    'id' => $id,
                    'fields' => $fields,
                ]
            )
        );
    }

    /**
     * Returns information about the role by its identifier
     *
     * @link https://apidocs.bitrix24.com/api-reference/document-generator/role/document-generator-role-get.html
     *
     * @throws BaseException
     * @throws TransportException
     */
    #[ApiEndpointMetadata(
        'documentgenerator.role.get',
        'https://apidocs.bitrix24.com/api-reference/document-generator/role/document-generator-role-get.html',
        'Returns information about the role by its identifier'
    )]
    public function get(int $id): RoleResult
    {
        return new RoleResult(
            $this->core->call('documentgenerator.role.get', ['id' => $id])
        );
    }

    /**
     * Returns a list of roles
     *
     * @link https://apidocs.bitrix24.com/api-reference/document-generator/role/document-generator-role-list.html
     *
     * @param int $start Offset for pagination
     *
     * @throws BaseException
     * @throws TransportException
     */
    #[ApiEndpointMetadata(
        'documentgenerator.role.list',
        'https://apidocs.bitrix24.com/api-reference/document-generator/role/document-generator-role-list.html',
        'Returns a list of roles'
    )]
    public function list(int $start = 0): RolesResult
    {
        return new RolesResult(
            $this->core->call(
                'documentgenerator.role.list',
                [
                    'start' => $start,
                ]
            )
        );
    }

    /**
     * Deletes a role
     *
     * @link https://apidocs.bitrix24.com/api-reference/document-generator/role/document-generator-role-delete.html
     *
     * @throws BaseException
     * @throws TransportException
     */
    #[ApiEndpointMetadata(
        'documentgenerator.role.delete',
        'https://apidocs.bitrix24.com/api-reference/document-generator/role/document-generator-role-delete.html',
        'Deletes a role'
    )]
    public function delete(int $id): DeletedRoleResult
    {
        return new DeletedRoleResult(
            $this->core->call(
                'documentgenerator.role.delete',
                ['id' => $id]
            )
        );
    }

    /**
     * Completely replaces the role-to-access-code binding map
     *
     * @link https://apidocs.bitrix24.com/api-reference/document-generator/role/document-generator-role-fill-accesses.html
     *
     * @param array<int, array{roleId: int, accessCode: string}> $accesses Array of role-to-access-code bindings
     *
     * @throws BaseException
     * @throws TransportException
     */
    #[ApiEndpointMetadata(
        'documentgenerator.role.fillaccesses',
        'https://apidocs.bitrix24.com/api-reference/document-generator/role/document-generator-role-fill-accesses.html',
        'Completely replaces the role-to-access-code binding map'
    )]
    public function fillAccesses(array $accesses): FillAccessesResult
    {
        return new FillAccessesResult(
            $this->core->call(
                'documentgenerator.role.fillaccesses',
                ['accesses' => $accesses]
            )
        );
    }

    /**
     * Count roles
     *
     * @throws BaseException
     * @throws TransportException
     */
    public function count(): int
    {
        return count($this->list()->getRoles());
    }
}
