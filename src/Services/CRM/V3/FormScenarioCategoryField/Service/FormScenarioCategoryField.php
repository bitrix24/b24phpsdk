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

namespace Bitrix24\SDK\Services\CRM\V3\FormScenarioCategoryField\Service;

use Bitrix24\SDK\Attributes\ApiEndpointMetadata;
use Bitrix24\SDK\Attributes\ApiServiceMetadata;
use Bitrix24\SDK\Core\Contracts\ApiVersion;
use Bitrix24\SDK\Core\Credentials\Scope;
use Bitrix24\SDK\Core\Exceptions\BaseException;
use Bitrix24\SDK\Core\Exceptions\TransportException;
use Bitrix24\SDK\Services\AbstractService;
use Bitrix24\SDK\Services\CRM\V3\FormScenarioCategoryField\Result\FormScenarioCategoryFieldResult;
use Bitrix24\SDK\Services\CRM\V3\FormScenarioCategoryField\Result\FormScenarioCategoryFieldsResult;

#[ApiServiceMetadata(new Scope(['crm']))]
class FormScenarioCategoryField extends AbstractService
{
    /**
     * @param non-empty-string $name
     * @param string[]         $select
     *
     * @throws BaseException
     * @throws TransportException
     *
     * @link https://apidocs.bitrix24.com/api-reference/crm/form/setup/crm-form-scenariocategory-field-get.html
     */
    #[ApiEndpointMetadata(
        'crm.form.scenariocategory.field.get',
        'https://apidocs.bitrix24.com/api-reference/crm/form/setup/crm-form-scenariocategory-field-get.html',
        'Returns metadata of a CRM form scenario category field by name',
        ApiVersion::v3
    )]
    public function get(string $name, array $select = []): FormScenarioCategoryFieldResult
    {
        $this->guardNonEmptyString($name, 'field name must not be empty');

        $params = ['name' => $name];
        if ($select !== []) {
            $params['select'] = $select;
        }

        return new FormScenarioCategoryFieldResult($this->core->call('crm.form.scenariocategory.field.get', $params, ApiVersion::v3));
    }

    /**
     * @param string[] $select
     *
     * @throws BaseException
     * @throws TransportException
     *
     * @link https://apidocs.bitrix24.com/api-reference/crm/form/setup/crm-form-scenariocategory-field-list.html
     */
    #[ApiEndpointMetadata(
        'crm.form.scenariocategory.field.list',
        'https://apidocs.bitrix24.com/api-reference/crm/form/setup/crm-form-scenariocategory-field-list.html',
        'Returns metadata of all CRM form scenario category fields',
        ApiVersion::v3
    )]
    public function list(array $select = []): FormScenarioCategoryFieldsResult
    {
        $params = $select !== [] ? ['select' => $select] : [];

        return new FormScenarioCategoryFieldsResult($this->core->call('crm.form.scenariocategory.field.list', $params, ApiVersion::v3));
    }
}
