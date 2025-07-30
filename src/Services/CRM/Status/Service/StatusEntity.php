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

namespace Bitrix24\SDK\Services\CRM\Status\Service;

use Bitrix24\SDK\Attributes\ApiEndpointMetadata;
use Bitrix24\SDK\Attributes\ApiServiceMetadata;
use Bitrix24\SDK\Core\Credentials\Scope;
use Bitrix24\SDK\Core\Exceptions\BaseException;
use Bitrix24\SDK\Core\Exceptions\TransportException;
use Bitrix24\SDK\Services\AbstractService;
use Bitrix24\SDK\Services\CRM\Status\Result\StatusEntitiesResult;
use Bitrix24\SDK\Services\CRM\Status\Result\StatusEntityTypesResult;

#[ApiServiceMetadata(new Scope(['crm']))]
class StatusEntity extends AbstractService
{
    /**
     * Returns elements of the reference book by its symbolic identifier.
     *
     * @link https://apidocs.bitrix24.com/api-reference/crm/status/crm-status-entity-items.html
     *
     * @param string $entityId
     * @throws BaseException
     * @throws TransportException
     */
    #[ApiEndpointMetadata(
        'crm.status.entity.items',
        'https://apidocs.bitrix24.com/api-reference/crm/status/crm-status-entity-items.html',
        'Returns elements of the reference book by its symbolic identifier.'
    )]
    public function items(string $entityId): StatusEntitiesResult
    {
        return new StatusEntitiesResult(
            $this->core->call(
                'crm.status.entity.items',
                [
                    'entityId' => $entityId,
                ]
            )
        );
    }


    /**
     * Returns descriptions of reference book types.
     *
     * @link https://apidocs.bitrix24.com/api-reference/crm/status/crm-status-entity-types.html
     *
     * @throws BaseException
     * @throws TransportException
     */
    #[ApiEndpointMetadata(
        'crm.status.entity.types',
        'https://apidocs.bitrix24.com/api-reference/crm/status/crm-status-entity-types.html',
        'Returns descriptions of reference book types.'
    )]
    public function types(): StatusEntityTypesResult
    {
        return new StatusEntityTypesResult(
            $this->core->call('crm.status.entity.types', [])
        );
    }
}
