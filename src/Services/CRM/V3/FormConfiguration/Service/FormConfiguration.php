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

namespace Bitrix24\SDK\Services\CRM\V3\FormConfiguration\Service;

use Bitrix24\SDK\Attributes\ApiEndpointMetadata;
use Bitrix24\SDK\Attributes\ApiServiceMetadata;
use Bitrix24\SDK\Core\Contracts\ApiVersion;
use Bitrix24\SDK\Core\Credentials\Scope;
use Bitrix24\SDK\Core\Exceptions\BaseException;
use Bitrix24\SDK\Core\Exceptions\TransportException;
use Bitrix24\SDK\Services\AbstractService;
use Bitrix24\SDK\Services\CRM\V3\FormConfiguration\Result\FormConfigurationResult;

#[ApiServiceMetadata(new Scope(['crm']))]
class FormConfiguration extends AbstractService
{
    /**
     * Returns the directories needed to build a CRM form (CRM schemes, categories, languages, field types, limits).
     *
     * @link https://apidocs.bitrix24.com/api-reference/crm/form/setup/crm-form-configuration-get.html
     *
     * @throws BaseException
     * @throws TransportException
     */
    #[ApiEndpointMetadata(
        'crm.form.configuration.get',
        'https://apidocs.bitrix24.com/api-reference/crm/form/setup/crm-form-configuration-get.html',
        'Returns the CRM form builder configuration',
        ApiVersion::v3
    )]
    public function get(): FormConfigurationResult
    {
        return new FormConfigurationResult($this->core->call('crm.form.configuration.get', [], ApiVersion::v3));
    }
}
