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

namespace Bitrix24\SDK\Services\Main\UserHistoryChangeField\Service;

use Bitrix24\SDK\Attributes\ApiEndpointMetadata;
use Bitrix24\SDK\Attributes\ApiServiceMetadata;
use Bitrix24\SDK\Core\Contracts\ApiVersion;
use Bitrix24\SDK\Core\Credentials\Scope;
use Bitrix24\SDK\Core\Exceptions\BaseException;
use Bitrix24\SDK\Core\Exceptions\TransportException;
use Bitrix24\SDK\Services\AbstractService;
use Bitrix24\SDK\Services\Main\UserHistoryChangeField\Result\UserHistoryChangeFieldResult;
use Bitrix24\SDK\Services\Main\UserHistoryChangeField\Result\UserHistoryChangeFieldsResult;

#[ApiServiceMetadata(new Scope(['main']))]
class UserHistoryChangeField extends AbstractService
{
    /**
     * Get metadata for one user history change field.
     *
     * @param string $name Field name, e.g. 'data'. Must not be blank.
     * @param string[] $select Descriptor properties to return: name, type, title, description,
     *                         validationRules, requiredGroups, filterable, sortable,
     *                         editable, editableGroups, multiple, elementType.
     *
     * @throws BaseException
     * @throws TransportException
     */
    #[ApiEndpointMetadata(
        'main.user.history.fields.field.get',
        'https://apidocs.bitrix24.com/api-reference/rest-v3.html',
        'Get metadata for one user history change field',
        ApiVersion::v3
    )]
    public function get(string $name, array $select = []): UserHistoryChangeFieldResult
    {
        $this->guardNonEmptyString($name, 'field name must not be empty');

        $parameters = ['name' => $name];
        if ($select !== []) {
            $parameters['select'] = $select;
        }

        return new UserHistoryChangeFieldResult(
            $this->core->call('main.user.history.fields.field.get', $parameters, ApiVersion::v3)
        );
    }

    /**
     * List metadata for all user history change fields.
     *
     * @param string[] $select Descriptor properties to return. Include name when using getFieldsDescription().
     *
     * @throws BaseException
     * @throws TransportException
     */
    #[ApiEndpointMetadata(
        'main.user.history.fields.field.list',
        'https://apidocs.bitrix24.com/api-reference/rest-v3.html',
        'List metadata for all user history change fields',
        ApiVersion::v3
    )]
    public function list(array $select = []): UserHistoryChangeFieldsResult
    {
        $parameters = $select === [] ? [] : ['select' => $select];

        return new UserHistoryChangeFieldsResult(
            $this->core->call('main.user.history.fields.field.list', $parameters, ApiVersion::v3)
        );
    }
}
