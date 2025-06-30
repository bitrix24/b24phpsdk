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

namespace Bitrix24\SDK\Services\CRM\Requisites\Service;

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
use Bitrix24\SDK\Services\CRM\Requisites\Result\RequisiteLinkResult;
use Bitrix24\SDK\Services\CRM\Requisites\Result\RequisiteLinksResult;
use Psr\Log\LoggerInterface;

#[ApiServiceMetadata(new Scope(['crm']))]
class RequisiteLink extends AbstractService
{
    /**
     * Registers the link between requisites and an object
     *
     * @link https://apidocs.bitrix24.com/api-reference/crm/requisites/links/crm-requisite-link-register.html
     *
     * @param array{
     *   ENTITY_TYPE_ID?: int,
     *   ENTITY_ID?: int,
     *   REQUISITE_ID?: int,
     *   BANK_DETAIL_ID?: int,
     *   MC_REQUISITE_ID?: int,
     *   MC_BANK_DETAIL_ID?: int,
     * } $fields
     *
     * @throws BaseException
     * @throws TransportException
     */
    #[ApiEndpointMetadata(
        'crm.requisite.link.register',
        'https://apidocs.bitrix24.com/api-reference/crm/requisites/links/crm-requisite-link-register.html',
        'Registers the link between requisites and an object'
    )]
    public function register(array $fields): UpdatedItemResult
    {
        return new UpdatedItemResult(
            $this->core->call(
                'crm.requisite.link.register',
                [
                    'fields' => $fields
                ]
            )
        );
    }

    /**
     * Removes the link between requisites and an object.
     *
     * @link https://apidocs.bitrix24.com/api-reference/crm/requisites/links/crm-requisite-link-unregister.html
     *
     *
     * @throws BaseException
     * @throws TransportException
     */
    #[ApiEndpointMetadata(
        'crm.requisite.link.unregister',
        'https://apidocs.bitrix24.com/api-reference/crm/requisites/links/crm-requisite-link-unregister.html',
        'Removes the link between requisites and an object'
    )]
    public function unregister(int $entityTypeId, int $entityId): DeletedItemResult
    {
        return new DeletedItemResult(
            $this->core->call(
                'crm.requisite.link.unregister',
                [
                    'entityTypeId' => $entityTypeId,
                    'entityId' => $entityId,
                ]
            )
        );
    }

    /**
     * Returns a formal description of the fields of the requisites link.
     *
     * @link https://apidocs.bitrix24.com/api-reference/crm/requisites/links/crm-requisite-link-fields.html
     *
     * @throws BaseException
     * @throws TransportException
     */
    #[ApiEndpointMetadata(
        'crm.requisite.link.fields',
        'https://apidocs.bitrix24.com/api-reference/crm/requisites/links/crm-requisite-link-fields.html',
        'Returns a formal description of the fields of the requisites link'
    )]
    public function fields(): FieldsResult
    {
        return new FieldsResult($this->core->call('crm.requisite.link.fields'));
    }

    /**
     * Returns the link between requisites and an object.
     *
     * @link https://apidocs.bitrix24.com/api-reference/crm/requisites/links/crm-requisite-link-get.html
     *
     *
     * @throws BaseException
     * @throws TransportException
     */
    #[ApiEndpointMetadata(
        'crm.requisite.link.get',
        'https://apidocs.bitrix24.com/api-reference/crm/requisites/links/crm-requisite-link-get.html',
        'Returns the link between requisites and an object'
    )]
    public function get(int $entityTypeId, int $entityId): RequisiteLinkResult
    {
        return new RequisiteLinkResult(
            $this->core->call(
                'crm.requisite.link.get',
                [
                    'entityTypeId' => $entityTypeId,
                    'entityId' => $entityId,
                ]
            )
        );
    }

    /**
     * Returns a list of links between requisites based on a filter.
     *
     * @link https://apidocs.bitrix24.com/api-reference/crm/requisites/links/crm-requisite-link-list.html
     *
     * @param array   $order     - order of link items
     * @param array   $filter    - filter array
     * @param array   $select    = ['ENTITY_TYPE_ID','ENTITY_ID','REQUISITE_ID','BANK_DETAIL_ID','MC_REQUISITE_ID','MC_BANK_DETAIL_ID']
     * @param integer $startItem - entity number to start from (usually returned in 'next' field of previous 'crm.requisite.link.list' API call)
     *
     * @throws BaseException
     * @throws TransportException
     */
    #[ApiEndpointMetadata(
        'crm.requisite.link.list',
        'https://apidocs.bitrix24.com/api-reference/crm/requisites/links/crm-requisite-link-list.html',
        'Returns a list of links between requisites based on a filter'
    )]
    public function list(array $order, array $filter, array $select, int $startItem = 0): RequisiteLinksResult
    {
        return new RequisiteLinksResult(
            $this->core->call(
                'crm.requisite.link.list',
                [
                    'order'  => $order,
                    'filter' => $filter,
                    'select' => $select,
                    'start'  => $startItem,
                ]
            )
        );
    }

    /**
     * Count links by filter
     *
     * @param array{
     *   ENTITY_TYPE_ID?: int,
     *   ENTITY_ID?: int,
     *   REQUISITE_ID?: int,
     *   BANK_DETAIL_ID?: int,
     *   MC_REQUISITE_ID?: int,
     *   MC_BANK_DETAIL_ID?: int,
     *   } $filter
     *
     * @throws \Bitrix24\SDK\Core\Exceptions\BaseException
     * @throws \Bitrix24\SDK\Core\Exceptions\TransportException
     */
    public function countByFilter(array $filter = []): int
    {
        return $this->list([], $filter, ['ENTITY_ID'], 1)->getCoreResponse()->getResponseData()->getPagination()->getTotal();
    }
}
