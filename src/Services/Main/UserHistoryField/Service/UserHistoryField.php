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

namespace Bitrix24\SDK\Services\Main\UserHistoryField\Service;

use Bitrix24\SDK\Attributes\ApiEndpointMetadata;
use Bitrix24\SDK\Attributes\ApiServiceMetadata;
use Bitrix24\SDK\Core\Contracts\ApiVersion;
use Bitrix24\SDK\Core\Credentials\Scope;
use Bitrix24\SDK\Core\Exceptions\BaseException;
use Bitrix24\SDK\Core\Exceptions\TransportException;
use Bitrix24\SDK\Services\AbstractService;
use Bitrix24\SDK\Services\Main\UserHistoryField\Result\UserHistoryFieldResult;
use Bitrix24\SDK\Services\Main\UserHistoryField\Result\UserHistoryFieldsResult;

#[ApiServiceMetadata(new Scope(['main']))]
class UserHistoryField extends AbstractService
{
    /**
     * Get metadata for one user history field.
     *
     * @param string $name Field name, e.g. 'dateInsert'. Must not be blank.
     * @param string[] $select Descriptor properties to return: name, type, title, description,
     *                         validationRules, requiredGroups, filterable, sortable,
     *                         editable, editableGroups, multiple, elementType.
     *
     * @throws BaseException
     * @throws TransportException
     */
    #[ApiEndpointMetadata(
        'main.user.history.field.get',
        'https://apidocs.bitrix24.com/api-reference/rest-v3.html',
        'Get metadata for one user history field',
        ApiVersion::v3
    )]
    public function get(string $name, array $select = []): UserHistoryFieldResult
    {
        $this->guardNonEmptyString($name, 'field name must not be empty');

        $parameters = ['name' => $name];
        if ($select !== []) {
            $parameters['select'] = $select;
        }

        return new UserHistoryFieldResult(
            $this->core->call('main.user.history.field.get', $parameters, ApiVersion::v3)
        );
    }

    /**
     * List metadata for all user history fields.
     *
     * @param string[] $select Descriptor properties to return. Include name when using getFieldsDescription().
     *
     * @throws BaseException
     * @throws TransportException
     */
    #[ApiEndpointMetadata(
        'main.user.history.field.list',
        'https://apidocs.bitrix24.com/api-reference/rest-v3.html',
        'List metadata for all user history fields',
        ApiVersion::v3
    )]
    public function list(array $select = []): UserHistoryFieldsResult
    {
        $parameters = $select === [] ? [] : ['select' => $select];

        return new UserHistoryFieldsResult(
            $this->core->call('main.user.history.field.list', $parameters, ApiVersion::v3)
        );
    }
}
