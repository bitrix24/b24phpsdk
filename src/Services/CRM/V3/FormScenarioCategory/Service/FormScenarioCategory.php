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

namespace Bitrix24\SDK\Services\CRM\V3\FormScenarioCategory\Service;

use Bitrix24\SDK\Attributes\ApiEndpointMetadata;
use Bitrix24\SDK\Attributes\ApiServiceMetadata;
use Bitrix24\SDK\Core\Contracts\ApiVersion;
use Bitrix24\SDK\Core\Credentials\Scope;
use Bitrix24\SDK\Core\Exceptions\BaseException;
use Bitrix24\SDK\Core\Exceptions\TransportException;
use Bitrix24\SDK\Services\AbstractService;
use Bitrix24\SDK\Services\CRM\V3\FormScenarioCategory\Result\ScenarioCategoriesResult;

#[ApiServiceMetadata(new Scope(['crm']))]
class FormScenarioCategory extends AbstractService
{
    /**
     * Returns CRM form scenario categories.
     *
     * @link https://apidocs.bitrix24.com/api-reference/crm/form/setup/crm-form-scenariocategory-list.html
     *
     * @throws BaseException
     * @throws TransportException
     */
    #[ApiEndpointMetadata(
        'crm.form.scenariocategory.list',
        'https://apidocs.bitrix24.com/api-reference/crm/form/setup/crm-form-scenariocategory-list.html',
        'Returns CRM form scenario categories',
        ApiVersion::v3
    )]
    public function list(): ScenarioCategoriesResult
    {
        return new ScenarioCategoriesResult($this->core->call('crm.form.scenariocategory.list', [], ApiVersion::v3));
    }
}
