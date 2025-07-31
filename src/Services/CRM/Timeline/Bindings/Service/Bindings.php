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

namespace Bitrix24\SDK\Services\CRM\Timeline\Bindings\Service;

use Bitrix24\SDK\Attributes\ApiEndpointMetadata;
use Bitrix24\SDK\Attributes\ApiServiceMetadata;
use Bitrix24\SDK\Core\Contracts\CoreInterface;
use Bitrix24\SDK\Core\Credentials\Scope;
use Bitrix24\SDK\Core\Exceptions\BaseException;
use Bitrix24\SDK\Core\Exceptions\TransportException;
use Bitrix24\SDK\Core\Result\AddedItemResult;
use Bitrix24\SDK\Core\Result\DeletedItemResult;
use Bitrix24\SDK\Core\Result\FieldsResult;
use Bitrix24\SDK\Core\Result\UpdatedItemResult;
use Bitrix24\SDK\Services\AbstractService;
use Bitrix24\SDK\Services\CRM\Timeline\Bindings\Result\BindingResult;
use Bitrix24\SDK\Services\CRM\Timeline\Bindings\Result\BindingsResult;
use Psr\Log\LoggerInterface;

#[ApiServiceMetadata(new Scope(['crm']))]
class Bindings extends AbstractService
{
    /**
     * Bindings constructor.
     */
    public function __construct(public Batch $batch, CoreInterface $core, LoggerInterface $logger)
    {
        parent::__construct($core, $logger);
    }

    /**
     * Adds a relationship between a timeline entry and a CRM entity.
     *
     * @link https://apidocs.bitrix24.com/api-reference/crm/timeline/bindings/crm-timeline-bindings-bind.html
     *
     *
     * @throws BaseException
     * @throws TransportException
     */
    #[ApiEndpointMetadata(
        'crm.timeline.bindings.bind',
        'https://apidocs.bitrix24.com/api-reference/crm/timeline/bindings/crm-timeline-bindings-bind.html',
        'Adds a relationship between a timeline entry and a CRM entity'
    )]
    public function bind(int $ownerId, int $entityId, string $entityType): UpdatedItemResult
    {
        return new UpdatedItemResult(
            $this->core->call(
                'crm.timeline.bindings.bind',
                [
                    'fields' => [
                        'OWNER_ID'    => $ownerId,
                        'ENTITY_ID'   => $entityId,
                        'ENTITY_TYPE' => $entityType,
                    ]
                ]
            )
        );
    }

    /**
     * Removes a relationship between a timeline entry and a CRM entity.
     *
     * @link https://apidocs.bitrix24.com/api-reference/crm/timeline/bindings/crm-timeline-bindings-unbind.html
     *
     * @throws BaseException
     * @throws TransportException
     */
    #[ApiEndpointMetadata(
        'crm.timeline.bindings.unbind',
        'https://apidocs.bitrix24.com/api-reference/crm/timeline/bindings/crm-timeline-bindings-unbind.html',
        'Removes a relationship between a timeline entry and a CRM entity'
    )]
    public function unbind(int $ownerId, int $entityId, string $entityType): DeletedItemResult
    {
        return new DeletedItemResult(
            $this->core->call(
                'crm.timeline.bindings.unbind',
                [
                    'fields' => [
                        'OWNER_ID'    => $ownerId,
                        'ENTITY_ID'   => $entityId,
                        'ENTITY_TYPE' => $entityType,
                    ]
                ]
            )
        );
    }

    /**
     * Retrieves fields of the relationship between CRM entities and timeline entries.
     *
     * @link https://apidocs.bitrix24.com/api-reference/crm/timeline/bindings/crm-timeline-bindings-fields.html
     *
     * @throws BaseException
     * @throws TransportException
     */
    #[ApiEndpointMetadata(
        'crm.timeline.bindings.fields',
        'https://apidocs.bitrix24.com/api-reference/crm/timeline/bindings/crm-timeline-bindings-fields.html',
        'Retrieves fields of the relationship between CRM entities and timeline entries'
    )]
    public function fields(): FieldsResult
    {
        return new FieldsResult($this->core->call('crm.timeline.bindings.fields'));
    }

    /**
     * Retrieves a list of relationships for a timeline entry.
     *
     * Can be used only with required filtration by OWNER_ID
     *
     * @link https://apidocs.bitrix24.com/api-reference/crm/timeline/bindings/crm-timeline-bindings-list.html
     *
     * @param array   $filter    - filter array
     * @param integer $startItem - entity number to start from (usually returned in 'next' field of previous 'crm.timeline.bindings.list' API call)
     *
     * @throws BaseException
     * @throws TransportException
     */
    #[ApiEndpointMetadata(
        'crm.timeline.bindings.list',
        'https://apidocs.bitrix24.com/api-reference/crm/timeline/bindings/crm-timeline-bindings-list.html',
        'Retrieves a list of relationships for a timeline entry'
    )]
    public function list(array $filter = [], int $startItem = 0): BindingsResult
    {
        return new BindingsResult(
            $this->core->call(
                'crm.timeline.bindings.list',
                [
                    'filter' => $filter,
                    'start'  => $startItem,
                ]
            )
        );
    }

    /**
     * Count bindings by filter
     *
     * Can be used only with required filtration by OWNER_ID
     *
     * @param array{
     *   OWNER_ID?: int,
     *   ENTITY_ID?: int,
     *   ENTITY_TYPE?: string,
     *   } $filter
     *
     * @throws \Bitrix24\SDK\Core\Exceptions\BaseException
     * @throws \Bitrix24\SDK\Core\Exceptions\TransportException
     */
    public function countByFilter(array $filter = []): int
    {
        return $this->list($filter, 1)->getCoreResponse()->getResponseData()->getPagination()->getTotal();
    }
}
