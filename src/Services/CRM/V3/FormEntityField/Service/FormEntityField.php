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

namespace Bitrix24\SDK\Services\CRM\V3\FormEntityField\Service;

use Bitrix24\SDK\Attributes\ApiEndpointMetadata;
use Bitrix24\SDK\Attributes\ApiServiceMetadata;
use Bitrix24\SDK\Core\Contracts\ApiVersion;
use Bitrix24\SDK\Core\Credentials\Scope;
use Bitrix24\SDK\Core\Exceptions\BaseException;
use Bitrix24\SDK\Core\Exceptions\InvalidArgumentException;
use Bitrix24\SDK\Core\Exceptions\TransportException;
use Bitrix24\SDK\Services\AbstractService;
use Bitrix24\SDK\Services\CRM\V3\FormEntityField\Result\EntityFieldsResult;

#[ApiServiceMetadata(new Scope(['crm']))]
class FormEntityField extends AbstractService
{
    /**
     * Returns CRM entity fields that can be placed on a form.
     *
     * The API requires a filter by entity, so at least one entity name is mandatory.
     *
     * @link https://apidocs.bitrix24.com/api-reference/crm/form/setup/crm-form-entityfield-list.html
     *
     * @param non-empty-list<string> $entityNames e.g. ['LEAD', 'CONTACT'], see entityNames of configuration entitySchemes
     * @throws InvalidArgumentException when no entity name is passed
     * @throws BaseException
     * @throws TransportException
     */
    #[ApiEndpointMetadata(
        'crm.form.entityfield.list',
        'https://apidocs.bitrix24.com/api-reference/crm/form/setup/crm-form-entityfield-list.html',
        'Returns CRM entity fields available for CRM forms',
        ApiVersion::v3
    )]
    public function list(array $entityNames): EntityFieldsResult
    {
        if ($entityNames === []) {
            throw new InvalidArgumentException('pass at least one entity name, the API requires a filter by entityName');
        }

        return new EntityFieldsResult(
            $this->core->call(
                'crm.form.entityfield.list',
                ['filter' => [['entityName', 'in', array_values($entityNames)]]],
                ApiVersion::v3
            )
        );
    }
}
