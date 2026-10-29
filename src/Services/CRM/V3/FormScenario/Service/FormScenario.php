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

namespace Bitrix24\SDK\Services\CRM\V3\FormScenario\Service;

use Bitrix24\SDK\Attributes\ApiEndpointMetadata;
use Bitrix24\SDK\Attributes\ApiServiceMetadata;
use Bitrix24\SDK\Core\Contracts\ApiVersion;
use Bitrix24\SDK\Core\Credentials\Scope;
use Bitrix24\SDK\Core\Exceptions\BaseException;
use Bitrix24\SDK\Core\Exceptions\TransportException;
use Bitrix24\SDK\Services\AbstractService;
use Bitrix24\SDK\Services\CRM\V3\FormScenario\Result\ScenariosResult;

#[ApiServiceMetadata(new Scope(['crm']))]
class FormScenario extends AbstractService
{
    /**
     * Returns CRM form scenarios.
     *
     * @link https://apidocs.bitrix24.com/api-reference/crm/form/setup/crm-form-scenario-list.html
     *
     * @throws BaseException
     * @throws TransportException
     */
    #[ApiEndpointMetadata(
        'crm.form.scenario.list',
        'https://apidocs.bitrix24.com/api-reference/crm/form/setup/crm-form-scenario-list.html',
        'Returns CRM form scenarios',
        ApiVersion::v3
    )]
    public function list(): ScenariosResult
    {
        return new ScenariosResult($this->core->call('crm.form.scenario.list', [], ApiVersion::v3));
    }
}
