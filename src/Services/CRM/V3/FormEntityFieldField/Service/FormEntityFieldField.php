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

namespace Bitrix24\SDK\Services\CRM\V3\FormEntityFieldField\Service;

use Bitrix24\SDK\Attributes\ApiEndpointMetadata;
use Bitrix24\SDK\Attributes\ApiServiceMetadata;
use Bitrix24\SDK\Core\Contracts\ApiVersion;
use Bitrix24\SDK\Core\Credentials\Scope;
use Bitrix24\SDK\Core\Exceptions\BaseException;
use Bitrix24\SDK\Core\Exceptions\TransportException;
use Bitrix24\SDK\Services\AbstractService;
use Bitrix24\SDK\Services\CRM\V3\FormEntityFieldField\Result\FormEntityFieldFieldResult;
use Bitrix24\SDK\Services\CRM\V3\FormEntityFieldField\Result\FormEntityFieldFieldsResult;

#[ApiServiceMetadata(new Scope(['crm']))]
class FormEntityFieldField extends AbstractService
{
    /**
     * @param non-empty-string $name
     * @param string[]         $select
     *
     * @throws BaseException
     * @throws TransportException
     *
     * @link https://apidocs.bitrix24.com/api-reference/crm/form/setup/crm-form-entityfield-field-get.html
     */
    #[ApiEndpointMetadata(
        'crm.form.entityfield.field.get',
        'https://apidocs.bitrix24.com/api-reference/crm/form/setup/crm-form-entityfield-field-get.html',
        'Returns metadata of a CRM form entity field field by name',
        ApiVersion::v3
    )]
    public function get(string $name, array $select = []): FormEntityFieldFieldResult
    {
        $this->guardNonEmptyString($name, 'field name must not be empty');

        $params = ['name' => $name];
        if ($select !== []) {
            $params['select'] = $select;
        }

        return new FormEntityFieldFieldResult($this->core->call('crm.form.entityfield.field.get', $params, ApiVersion::v3));
    }

    /**
     * @param string[] $select
     *
     * @throws BaseException
     * @throws TransportException
     *
     * @link https://apidocs.bitrix24.com/api-reference/crm/form/setup/crm-form-entityfield-field-list.html
     */
    #[ApiEndpointMetadata(
        'crm.form.entityfield.field.list',
        'https://apidocs.bitrix24.com/api-reference/crm/form/setup/crm-form-entityfield-field-list.html',
        'Returns metadata of all CRM form entity field fields',
        ApiVersion::v3
    )]
    public function list(array $select = []): FormEntityFieldFieldsResult
    {
        $params = $select !== [] ? ['select' => $select] : [];

        return new FormEntityFieldFieldsResult($this->core->call('crm.form.entityfield.field.list', $params, ApiVersion::v3));
    }
}
